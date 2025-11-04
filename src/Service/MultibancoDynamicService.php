<?php

namespace Ifthenpay\PaymentGateway\Service;

use Ifthenpay\PaymentGateway\Config;
use Ifthenpay\PaymentGateway\Enums\MethodCode;
use Ifthenpay\PaymentGateway\Enums\Status;
use Ifthenpay\PaymentGateway\Exception\EndpointResponseException;
use Ifthenpay\PaymentGateway\Exception\WebhookValidationException;
use Ifthenpay\PaymentGateway\Interface\Service\MultibancoDynamicServiceInterface;
use Ifthenpay\PaymentGateway\Model\MultibancoDynamic;
use Ifthenpay\PaymentGateway\RequestObj\MultibancoDynamicInitRequest;
use Ifthenpay\PaymentGateway\RequestObj\WebhookRequest;
use Ifthenpay\PaymentGateway\Utils\DateTools;
use Ifthenpay\PaymentGateway\Utils\StringTools;

class MultibancoDynamicService implements MultibancoDynamicServiceInterface
{
    public const INIT_STATUS_SUCCESS = '0'; // Request initialized successfully (pending acceptance).
    public const INIT_STATUS_ERROR   = '-1'; // Error initializing the request.

    private string $key;
    private Config $config;
    private ApiService $apiService;
    private WebhookService $webhookService;
    private PaymentService $paymentService;

    public function __construct(
        Config $config,
        ApiService $apiService,
        WebhookService $webhookService,
        PaymentService $paymentService
    ) {
        $this->key            = $config->multibancoDynamicKey();
        $this->config         = $config;
        $this->apiService     = $apiService;
        $this->webhookService = $webhookService;
        $this->paymentService = $paymentService;
    }



    /**
     * Initializes a Multibanco dynamic payment.
     *
     * @param string      $orderId       The unique identifier for the order.
     * @param string      $amount        The payment amount.
     * @param string|null $description   Payment description.
     * @param int|null    $daysToExpire  Number of days until the payment expires. If not provided, uses the value from configuration.
     *
     * @return MultibancoDynamic Returns a MultibancoDynamic object containing payment details if successful.
     *
     * @throws EndpointResponseException If the response from the payment endpoint is unexpected or indicates an error.
     */
    public function initPayment(string $orderId, string $amount, ?string $description = null, ?int $daysToExpire = null): MultibancoDynamic
    {
        $daysToExpire = $daysToExpire ?? $this->config->multibancoDynamicDaysToExpire();

        $request = new MultibancoDynamicInitRequest(
            $this->key,
            $orderId,
            $amount,
            $description,
            $daysToExpire
        );
        $responseObj = $this->apiService->initMultibancoPayment($request);
        $response    = json_decode((string) $responseObj->getBody(), true);

        if (
            !isset($response['Status']) || !isset($response['Amount']) || !isset($response['OrderId']) || !isset($response['RequestId']) || !isset($response['Reference']) || !isset($response['ExpiryDate']) || !isset($response['Message'])
        ) {
            throw new EndpointResponseException('Multibanco payment request returned unexpected response.', ['request' => $request, 'response' => $response]);
        }

        if ($response['Status'] === self::INIT_STATUS_SUCCESS) {

            return new MultibancoDynamic($response['Amount'], $response['OrderId'], $response['Entity'], $response['Reference'], $response['RequestId'], Status::PENDING, $this->expireDaysToDate($daysToExpire), DateTools::getTimeStamp());
        }


        if ($response['Status'] === self::INIT_STATUS_ERROR) {
            throw new EndpointResponseException('Multibanco payment request returned error with message: ' . $response['Message'], ['request' => $request, 'response' => $response]);
        }

        throw new EndpointResponseException('Multibanco payment request returned unexpected response.', ['request' => $request, 'response' => $response]);
    }



    /**
     * Registers a webhook URL for the multibanco dynamic account set in config.
     *
     * Purpose: This method is used to set up a webhook URL that ifthenpay will call to when a payment occurs for the multibanco dynamic account set in the config.
     * This is used to update the payment status in real-time without needing to poll the API for status updates, and is the preferred method for tracking payment status.
     * Calling this method will overwrite any previously set webhook URL for the multibanco dynamic account.
     *
     * @param string $webhookUrl The URL to register as a webhook for multibanco dynamic callbacks.
     * @param array<string, string>|null $extraParams Optional additional query parameters placeholders to include in the webhook URL.
     * @return string The full webhook URL with query parameters that was registered.
     * @throws EndpointResponseException If the API response is unexpected or registration fails.
     */
    public function registerWebhook(string $webhookUrl, ?array $extraParams = null): string
    {
        $webhookParams        = $extraParams ? array_merge($this->webhookService->getWebhookParams(MethodCode::MULTIBANCO_DYNAMIC), $extraParams) : $this->webhookService->getWebhookParams(MethodCode::MULTIBANCO_DYNAMIC);
        $webhookUrlWithParams = StringTools::addQueryStringVars($webhookUrl, $webhookParams);

        $this->webhookService->registerWebhook(MethodCode::MULTIBANCO_DYNAMIC->value, $this->key, $webhookUrlWithParams);

        return $webhookUrlWithParams;
    }



    /**
     * Validates the incoming webhook request to ensure its authenticity.
     *
     * Purpose: Used to protect against fraudulent or tampered webhook requests.
     * You are required to convert the parameters received in the webhook to a WebhookRequest object to pass it in the function.
     *
     * @param WebhookRequest $webhookRequest The incoming webhook request data.
     * @param MultibancoDynamic $payment The payment that is being compared.
     * @return void
     * @throws WebhookValidationException If the webhook validation fails due to missing parameters or mismatched values.
     */
    public function validateWebhook(WebhookRequest $webhookRequest, MultibancoDynamic $payment): void
    {
        $paymentArray = array_merge($payment->toArray(), ['antiPhishingKey' => $this->config->antiPhishingKey()]);

        $this->webhookService->validateWebhook($webhookRequest->toArray(), $paymentArray, ['tid' => 'transactionId']);
    }



    /**
     * Checks if the MultibancoDynamic payment is complete/paid.
     *
     * Purpose: This method is used as an alternative to the webhook, in order to verify if a specific MultibancoDynamic payment has been successfully paid.
     * This can be used if you encounter issues with webhooks or need to double-check the payment status for a specific transaction.
     *
     * @param MultibancoDynamic $payment The MultibancoDynamic payment object containing transaction details.
     * @return bool Returns true if the payment is complete, false otherwise.
     * @throws EndpointResponseException If the API response indicates an error or unexpected content.
     */
    public function isPaid(MultibancoDynamic $payment): bool
    {
        return $this->paymentService->isPaid($payment);
    }



    /**
     * Checks if the MultibancoDynamic payment is expired.
     *
     * Purpose: This method is used to verify if a specific MultibancoDynamic payment has expired based on its expiry date.
     *
     * @param MultibancoDynamic $payment The MultibancoDynamic payment object containing transaction details.
     * @return bool Returns true if the payment is expired, false otherwise.
     */
    public function isExpired(MultibancoDynamic $payment): bool
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
