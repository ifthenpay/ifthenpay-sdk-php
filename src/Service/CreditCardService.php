<?php

namespace Ifthenpay\PaymentGateway\Service;

use Ifthenpay\PaymentGateway\Config;
use Ifthenpay\PaymentGateway\Enums\Status;
use Ifthenpay\PaymentGateway\Enums\MethodCode;
use Ifthenpay\PaymentGateway\Exception\WebhookValidationException;
use Ifthenpay\PaymentGateway\Exception\EndpointResponseException;
use Ifthenpay\PaymentGateway\Interface\Service\CreditCardServiceInterface;
use Ifthenpay\PaymentGateway\Model\CreditCard;
use Ifthenpay\PaymentGateway\RequestObj\CreditCardInitRequest;
use Ifthenpay\PaymentGateway\RequestObj\WebhookRequest;
use Ifthenpay\PaymentGateway\Utils\DateTools;
use Ifthenpay\PaymentGateway\Utils\StringTools;

class CreditCardService implements CreditCardServiceInterface
{
    public const INIT_STATUS_SUCCESS = '0';
    public const INIT_STATUS_ERROR   = '-1';

    private string $key;
    private ApiService $apiService;
    private WebhookService $webhookService;
    private PaymentService $paymentService;
    private Config $config;


    public function __construct(
        Config $config,
        ApiService $apiService,
        WebhookService $webhookService,
        PaymentService $paymentService
    ) {
        $this->key            = $config->creditCardKey();
        $this->config         = $config;
        $this->apiService     = $apiService;
        $this->webhookService = $webhookService;
        $this->paymentService = $paymentService;
    }


    /**
     * Initializes a credit card payment request.
     *
     * @param string      $orderId     The unique identifier for the order.
     * @param string      $amount      The payment amount.
     * @param string|null $successUrl  URL to redirect upon successful payment, if not provided will use configured value.
     * @param string|null $errorUrl    URL to redirect upon payment error, if not provided will use configured value.
     * @param string|null $cancelUrl   URL to redirect if payment is cancelled, if not provided will use configured value.
     * @param string|null $language    language code for the payment interface, if not provided will use configured value.
     *
     * @return CreditCard Returns a CreditCard object with payment details if initialization is successful.
     *
     * @throws EndpointResponseException If the response from the payment endpoint is unexpected or indicates an error.
     */

    public function initPayment(string $orderId, string $amount, ?string $successUrl = null, ?string $errorUrl = null, ?string $cancelUrl = null, ?string $language = null): CreditCard
    {

        $request = new CreditCardInitRequest(
            $this->key,
            $orderId,
            $amount,
            $successUrl ?? $this->config->creditCardSuccessUrl(),
            $errorUrl ?? $this->config->creditCardErrorUrl(),
            $cancelUrl ?? $this->config->creditCardCancelUrl(),
            $language ?? $this->config->language(),
        );
        $responseObj = $this->apiService->initCreditCardPayment($request);

        $response = json_decode((string) $responseObj->getBody(), true);

        if (
            !isset($response['Status']) || !isset($response['RequestId']) || !isset($response['PaymentUrl'])
        ) {
            throw new EndpointResponseException('CreditCard payment request returned unexpected response.', ['request' => $request, 'response' => $response]);
        }

        if ($response['Status'] === self::INIT_STATUS_SUCCESS) {
            return new CreditCard($request->amount, $request->orderId, $response['RequestId'], $response['PaymentUrl'], Status::PENDING, $this->expireMinutesToDate(), DateTools::getTimeStamp());
        }

        if ($response['Status'] === self::INIT_STATUS_ERROR) {
            throw new EndpointResponseException('Error initializing the request.', ['request' => $request, 'response' => $response]);
        }

        throw new EndpointResponseException('CreditCard payment request returned unexpected response.', ['request' => $request, 'response' => $response]);
    }


    /**
     * Validates the incoming webhook request to ensure its authenticity.
     *
     * Purpose: Used to protect against fraudulent or tampered webhook requests.
     * You are required to convert the parameters received in the webhook to a WebhookRequest object to pass it in the function.
     *
     * @param WebhookRequest $webhookRequest The incoming webhook request data.
     * @param CreditCard $payment The payment that is being compared.
     * @return void
     * @throws WebhookValidationException If the webhook validation fails due to missing parameters or mismatched values.
     */
    public function validateWebhook(WebhookRequest $webhookRequest, CreditCard $payment): void
    {
        $paymentArray = array_merge($payment->toArray(), ['antiPhishingKey' => $this->config->antiPhishingKey()]);

        $this->webhookService->validateWebhook($webhookRequest->toArray(), $paymentArray, ['tid' => 'transactionId']);
    }



    /**
     * Registers a webhook URL for the Credit Card account set in config.
     *
     * Purpose: This method is used to set up a webhook URL that ifthenpay will call to when a payment occurs for the Credit Card account set in the config.
     * This is used to update the payment status in real-time without needing to poll the API for status updates, and is the preferred method for tracking payment status.
     * Calling this method will overwrite any previously set webhook URL for the Credit Card account.
     *
     * @param string $webhookUrl The URL to register as a webhook for Credit Card callbacks.
     * @param array<string, string>|null $extraParams Optional additional query parameters placeholders to include in the webhook URL.
     * @return string The full webhook URL with query parameters that was registered.
     * @throws EndpointResponseException If the API response is unexpected or registration fails.
     */
    public function registerWebhook(string $webhookUrl, ?array $extraParams = null): string
    {
        $webhookParams        = $extraParams ? array_merge($this->webhookService->getWebhookParams(MethodCode::CREDIT_CARD), $extraParams) : $this->webhookService->getWebhookParams(MethodCode::CREDIT_CARD);
        $webhookUrlWithParams = StringTools::addQueryStringVars($webhookUrl, $webhookParams);

        $this->webhookService->registerWebhook(MethodCode::CREDIT_CARD->value, $this->key, $webhookUrlWithParams);

        return $webhookUrlWithParams;
    }



    /**
     * Checks if the Credit Card payment is complete/paid.
     *
     * Purpose: This method is used as an alternative to the webhook, in order to verify if a specific Credit Card payment has been successfully paid.
     * This can be used if you encounter issues with webhooks or need to double-check the payment status for a specific transaction.
     *
     * @param CreditCard $payment The Credit Card payment object containing transaction details.
     * @return bool Returns true if the payment is complete, false otherwise.
     * @throws EndpointResponseException If the API response indicates an error or unexpected content.
     */
    public function isPaid(CreditCard $payment): bool
    {
        return $this->paymentService->isPaid($payment);
    }



    /**
     * Verifies the payment by validating the provided secret key "sk" against an expected signature.
     *
     * The expected signature is generated using HMAC-SHA256 with the order ID, amount, and transaction ID,
     * and the service's key. If the provided secret key does not match the expected signature,
     * a WebhookValidationException is thrown.
     *
     * @param string $secretKey The secret key to validate. Received when redirecting back from the payment gateway to the success url under the "sk" parameter.
     * @param CreditCard $payment The payment object.
     *
     * @throws WebhookValidationException If the secret key does not match the expected signature.
     */
    public function verifyPayment(string $secretKey, CreditCard $payment): void
    {
        $expectedSk = hash_hmac('sha256', $payment->orderId . $payment->amount . $payment->transactionId, $this->key);
        if ($secretKey !== $expectedSk) {
            throw new WebhookValidationException('CreditCard return validation failed: invalid signature.', ['expectedSk' => $expectedSk, 'receivedSk' => $secretKey]);
        }
    }



    /**
     * Checks if the payment has expired.
     *
     * @param CreditCard $payment Payment instance to check.
     * @return bool Returns true if the payment is expired, false if not expired or if expiration is not set.
     */
    public function isExpired(CreditCard $payment): bool
    {
        return $this->paymentService->isExpired($payment);
    }



    /**
     * Calculates the expiration date based on the configured number of minutes to expire.
     *
     * Retrieves the number of minutes to expire from the configuration. If not set, returns null.
     * Otherwise, returns a future date calculated by adding the specified minutes to the current time.
     *
     * @return \DateTimeImmutable|null The calculated expiration date, or null if not configured.
     */
    private function expireMinutesToDate(): ?\DateTimeImmutable
    {
        $minutesToExpire = $this->config->creditCardMinutesToExpire();
        if ($minutesToExpire === null) {
            return null;
        }
        return DateTools::getFutureDate(0, 0, $minutesToExpire);
    }
}
