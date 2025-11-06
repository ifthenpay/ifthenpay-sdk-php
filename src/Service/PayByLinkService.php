<?php

namespace Ifthenpay\PaymentGateway\Service;

use Ifthenpay\PaymentGateway\Config;
use Ifthenpay\PaymentGateway\Enums\Status;
use Ifthenpay\PaymentGateway\Enums\MethodCode;
use Ifthenpay\PaymentGateway\Exception\WebhookValidationException;
use Ifthenpay\PaymentGateway\Exception\EndpointResponseException;
use Ifthenpay\PaymentGateway\Exception\WebhookServiceException;
use Ifthenpay\PaymentGateway\Interface\Service\PayByLinkServiceInterface;
use Ifthenpay\PaymentGateway\Model\PayByLink;
use Ifthenpay\PaymentGateway\RequestObj\PayByLinkInitRequest;
use Ifthenpay\PaymentGateway\RequestObj\WebhookRequest;
use Ifthenpay\PaymentGateway\Utils\DateTools;
use Ifthenpay\PaymentGateway\Utils\StringTools;

class PayByLinkService implements PayByLinkServiceInterface
{
    private string $key;
    private ApiService $apiService;
    private WebhookService $webhookService;
    private PaymentService $paymentService;
    private Config $config;

    private const METHOD_INDEX = [
        MethodCode::MULTIBANCO_DYNAMIC->value => '1',
        MethodCode::MULTIBANCO_OFFLINE->value  => '1',
        MethodCode::MBWAY->value              => '2',
        MethodCode::PAYSHOP->value            => '3',
        MethodCode::CREDIT_CARD->value        => '4',
        MethodCode::COFIDIS->value            => '5',
        MethodCode::GOOGLE->value             => '6',
        MethodCode::APPLE->value              => '7',
        MethodCode::PIX->value                => '8',
    ];

    public function __construct(
        Config $config,
        ApiService $apiService,
        WebhookService $webhookService,
        PaymentService $paymentService
    ) {
        $this->key            = $config->payByLinkKey();
        $this->config         = $config;
        $this->apiService     = $apiService;
        $this->webhookService = $webhookService;
        $this->paymentService = $paymentService;
    }



    /**
     * Initializes a PayByLink payment request.
     *
     * @param string      $orderId      The unique identifier for the order.
     * @param string      $amount       The payment amount.
     * @param string|null $description  Payment description.
     * @param string|null $successUrl   URL to redirect upon successful payment, on success redirect it will append a 'tid' query parameter with the transaction ID.
     * @param string|null $errorUrl     URL to redirect upon payment error.
     * @param string|null $cancelUrl    URL to redirect upon payment cancellation.
     * @param string|null $returnUrl    URL to redirect after payment process.
     * @param string|null $language     Language code for the payment gateway page.
     * @param int|null    $daysToExpire Number of days until the payment link expires.
     *
     * @return PayByLink Returns a PayByLink payment object.
     *
     * @throws EndpointResponseException If the API response is missing required fields or contains null values.
     */
    public function initPayment(
        string $orderId,
        string $amount,
        ?string $description = null,
        ?string $successUrl = null,
        ?string $errorUrl = null,
        ?string $cancelUrl = null,
        ?string $returnUrl = null,
        ?string $language = null,
        ?int $daysToExpire = null
    ): PayByLink {

        $methodArr         = $this->config->payByLinkMethodAccounts();
        $methodAccountsStr = implode(';', array_map(
            fn($k, $v) => "$k|$v",
            array_keys($methodArr),
            $methodArr
        ));

        // append tid placeholder to success url, in order to receive it after payment
        $successUrl = $successUrl ?? $this->config->payByLinkSuccessUrl();
        if ($successUrl) {
            $successUrl = StringTools::addQueryStringVars($successUrl, ['tid' => '[TRANSACTIONID]']);
        }

        $daysToExpire = $daysToExpire ?? $this->config->payByLinkDaysToExpire();

        $request = new PayByLinkInitRequest(
            $this->key,
            $orderId,
            $amount,
            $methodAccountsStr,
            $this->config->payByLinkDefaultMethod() ? self::METHOD_INDEX[$this->config->payByLinkDefaultMethod()] : null,
            $daysToExpire,
            $successUrl,
            $errorUrl ?? $this->config->payByLinkErrorUrl(),
            $cancelUrl ?? $this->config->payByLinkCancelUrl(),
            $returnUrl ?? $this->config->payByLinkBtnCloseUrl(),
            $description,
            $this->config->payByLinkBtnCloseLabel(),
            $language ?? $this->config->language(),
            $this->config->payByLinkIsOneTimePayment()
        );

        $responseObj = $this->apiService->initPayByLinkPayment($request);

        $response = json_decode((string) $responseObj->getBody(), true);

        if (
            !array_key_exists('PinCode', $response) || !array_key_exists('PinpayUrl', $response) || !array_key_exists('RedirectUrl', $response)
        ) {
            throw new EndpointResponseException('PayByLink payment request returned unexpected response.', ['request' => $request, 'response' => $response]);
        }

        if ($response['PinCode'] === null || $response['PinpayUrl'] === null || $response['RedirectUrl'] === null) {
            throw new EndpointResponseException('PayByLink payment request returned null values. Possible invalid key, amount, or orderId.', ['request' => $request, 'response' => $response]);
        }

        if (!empty($response['PinCode']) && !empty($response['PinpayUrl']) && !empty($response['RedirectUrl'])) {

            return new PayByLink(
                $amount,
                $orderId,
                $response['PinCode'],
                $response['PinpayUrl'],
                Status::PENDING,
                $this->expireDaysToDate($daysToExpire),
                DateTools::getTimeStamp()
            );
        }

        throw new EndpointResponseException('PayByLink payment request returned unexpected response.', ['request' => $request, 'response' => $response]);
    }



    /**
     * Validates the incoming webhook request to ensure its authenticity.
     *
     * Purpose: Used to protect against fraudulent or tampered webhook requests.
     * You are required to convert the parameters received in the webhook to a WebhookRequest object to pass it in the function.
     *
     * @param WebhookRequest $webhookRequest The incoming webhook request data.
     * @param PayByLink $payment The payment that is being compared.
     * @return void
     * @throws WebhookValidationException If the webhook validation fails due to missing parameters or mismatched values.
     */
    public function validateWebhook(WebhookRequest $webhookRequest, PayByLink $payment): void
    {
        $paymentArray = array_merge($payment->toArray(), ['antiPhishingKey' => $this->config->antiPhishingKey()]);

        $this->webhookService->validateWebhook($webhookRequest->toArray(), $paymentArray);
    }



    /**
     * Registers a webhook URL for the PayByLink account set in config.
     *
     * Purpose: This method is used to set up a webhook URL that ifthenpay will call to when a payment occurs for the PayByLink account set in the config.
     * This is used to update the payment status in real-time without needing to poll the API for status updates, and is the preferred method for tracking payment status.
     * Calling this method will overwrite any previously set webhook URL for the PayByLink account.
     *
     * @param string $webhookUrl The URL to register as a webhook for PayByLink callbacks.
     * @param array<string, string>|null $extraParams Optional additional query parameters placeholders to include in the webhook URL.
     * @return string The full webhook URL with query parameters that was registered.
     * @throws EndpointResponseException If the API response is unexpected or registration fails.
     */
    public function registerWebhook(string $webhookUrl, ?array $extraParams = null): string
    {
        $webhookParams        = $extraParams ? array_merge($this->webhookService->getWebhookParams(MethodCode::PAY_BY_LINK), $extraParams) : $this->webhookService->getWebhookParams(MethodCode::PAY_BY_LINK);
        $webhookUrlWithParams = StringTools::addQueryStringVars($webhookUrl, $webhookParams);


        $methodAccounts = $this->config->payByLinkMethodAccounts();
        foreach ($methodAccounts as $code => $key) {

            if (MethodCode::tryFrom($code) || preg_match('/^\d{5}$/', $code)) {
                $this->webhookService->registerWebhook($code, $key, $webhookUrlWithParams);
            } else {
                throw new WebhookServiceException('Invalid method code for PayByLink webhook registration.', ['methodCode' => $code, 'key' => $key]);
            }
        }

        return $webhookUrlWithParams;
    }



    /**
     * Checks if a transaction has been paid based on its transaction ID.
     * Requires you to have the transaction ID to check, which is usually received via success redirect or webhook.
     *
     * @param string $transactionId The unique identifier of the transaction.
     * @return bool|MethodCode Returns false if the transaction is not paid or invalid,
     *                        otherwise returns the payment method code.
     */
    public function isTransactionPaid(string $transactionId): bool|MethodCode
    {
        $responseObj = $this->apiService->getPayByLinkPaymentStatus($transactionId);
        $response    = json_decode((string) $responseObj->getBody(), true);

        if (!isset($response['TransactionId']) || !isset($response['PaymentMethod'])) {
            return false;
        }

        // multibanco is the only exception where the method code returned is different, because both offline and dynamic use the same code in the API
        $paymentMethod = $response['PaymentMethod'];
        if ($paymentMethod === 'MULTIBANCO') {

            $paymentAccounts = $this->config->payByLinkMethodAccounts();
            $paymentMethod   = MethodCode::MULTIBANCO_OFFLINE;

            foreach ($paymentAccounts as $key => $value) {
                if ($key === MethodCode::MULTIBANCO_DYNAMIC->value) {
                    $paymentMethod = MethodCode::MULTIBANCO_DYNAMIC;
                    break;
                }
            }
        }

        return $paymentMethod;
    }



    /**
     * Checks if the payment has expired.
     *
     * @param PayByLink $payment Payment instance to check.
     * @return bool Returns true if the payment is expired, false if not expired or if expiration is not set.
     */
    public function isExpired(PayByLink $payment): bool
    {
        return $this->paymentService->isExpired($payment);
    }



    /**
     * Calculates the expiration date based on the number of days to expire.
     * If no expiration days are provided, returns null.
     *
     * @param int|null $daysToExpire Number of days until expiration, or null for no expiration.
     * @return \DateTimeImmutable|null The calculated expiration date, or null if not applicable.
     */
    private function expireDaysToDate(?int $daysToExpire): ?\DateTimeImmutable
    {
        if ($daysToExpire === null) {
            return null;
        }
        return DateTools::getFutureDate($daysToExpire, 23, 59, true);
    }
}
