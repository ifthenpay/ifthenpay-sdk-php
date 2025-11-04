<?php

namespace Ifthenpay\PaymentGateway\Service;

use Ifthenpay\PaymentGateway\Config;
use Ifthenpay\PaymentGateway\Enums\Status;
use Ifthenpay\PaymentGateway\Enums\MethodCode;
use Ifthenpay\PaymentGateway\Exception\WebhookValidationException;
use Ifthenpay\PaymentGateway\Exception\EndpointResponseException;
use Ifthenpay\PaymentGateway\Interface\Service\PayshopServiceInterface;
use Ifthenpay\PaymentGateway\Model\Payshop;
use Ifthenpay\PaymentGateway\RequestObj\PayshopInitRequest;
use Ifthenpay\PaymentGateway\RequestObj\WebhookRequest;
use Ifthenpay\PaymentGateway\Utils\DateTools;
use Ifthenpay\PaymentGateway\Utils\StringTools;

class PayshopService implements PayshopServiceInterface
{
    public const INIT_STATUS_SUCCESS             = '0'; // Request initialized successfully (pending acceptance).
    public const INIT_STATUS_INVALID_KEY         = '102'; // Invalid payshop account key.
    public const INIT_STATUS_INVALID_PARAM_VALUE = '103'; // missing parameters.

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
        $this->key            = $config->payshopKey();
        $this->config         = $config;
        $this->apiService     = $apiService;
        $this->webhookService = $webhookService;
        $this->paymentService = $paymentService;
    }




    /**
     * Initializes a Payshop payment request.
     *
     * @param string   $orderId       The unique identifier for the order.
     * @param string   $amount        The payment amount.
     * @param int|null $daysToExpire  Number of days until the payment expires. If not provided, uses default from config.
     *
     * @return Payshop Returns a Payshop payment object if the request is successful.
     *
     * @throws EndpointResponseException If the response is missing required fields, contains invalid parameter values, or an invalid payshop account key.
     */
    public function initPayment(string $orderId, string $amount, ?int $daysToExpire = null): Payshop
    {
        $daysToExpire = $daysToExpire ?? $this->config->payshopDaysToExpire();

        $request = new PayshopInitRequest(
            $this->key,
            $orderId,
            $amount,
            $daysToExpire
        );
        $responseObj = $this->apiService->initPayshopPayment($request);
        $response    = json_decode((string) $responseObj->getBody(), true);

        if (
            !isset($response['Code']) || !isset($response['RequestId']) || !isset($response['Reference'])
        ) {
            throw new EndpointResponseException('Payshop payment request returned unexpected response missing fields.', ['request' => $request, 'response' => $response]);
        }

        if ($response['Code'] === self::INIT_STATUS_SUCCESS) {

            return new Payshop($amount, $orderId, $response['RequestId'], $response['Reference'], Status::PENDING, $this->expireDaysToDate($daysToExpire), DateTools::getTimeStamp());
        }

        if ($response['Code'] === self::INIT_STATUS_INVALID_PARAM_VALUE) {
            throw new EndpointResponseException('Invalid parameter value.', ['request' => $request, 'response' => $response]);
        }

        if ($response['Code'] === self::INIT_STATUS_INVALID_KEY) {
            throw new EndpointResponseException('Invalid payshop account key.', ['request' => $request, 'response' => $response]);
        }

        throw new EndpointResponseException('Payshop payment request returned unexpected response.', ['request' => $request, 'response' => $response]);
    }


    /**
     * Validates the incoming webhook request to ensure its authenticity.
     *
     * Purpose: Used to protect against fraudulent or tampered webhook requests.
     * You are required to convert the parameters received in the webhook to a WebhookRequest object to pass it in the function.
     *
     * @param WebhookRequest $webhookRequest The incoming webhook request data.
     * @param Payshop $payment The payment that is being compared.
     * @return void
     * @throws WebhookValidationException If the webhook validation fails due to missing parameters or mismatched values.
     */
    public function validateWebhook(WebhookRequest $webhookRequest, Payshop $payment): void
    {
        $paymentArray = array_merge($payment->toArray(), ['antiPhishingKey' => $this->config->antiPhishingKey()]);

        $this->webhookService->validateWebhook($webhookRequest->toArray(), $paymentArray, ['tid' => 'transactionId']);
    }



    /**
     * Registers a webhook URL for the PAYSHOP account set in config.
     *
     * Purpose: This method is used to set up a webhook URL that ifthenpay will call to when a payment occurs for the PAYSHOP account set in the config.
     * This is used to update the payment status in real-time without needing to poll the API for status updates, and is the preferred method for tracking payment status.
     * Calling this method will overwrite any previously set webhook URL for the PAYSHOP account.
     *
     * @param string $webhookUrl The URL to register as a webhook for PAYSHOP callbacks.
     * @param array<string, string>|null $extraParams Optional additional query parameters placeholders to include in the webhook URL.
     * @return string The full webhook URL with query parameters that was registered.
     * @throws EndpointResponseException If the API response is unexpected or registration fails.
     */
    public function registerWebhook(string $webhookUrl, ?array $extraParams = null): string
    {
        $webhookParams        = $extraParams ? array_merge($this->webhookService->getWebhookParams(MethodCode::PAYSHOP), $extraParams) : $this->webhookService->getWebhookParams(MethodCode::PAYSHOP);
        $webhookUrlWithParams = StringTools::addQueryStringVars($webhookUrl, $webhookParams);

        $this->webhookService->registerWebhook(MethodCode::PAYSHOP->value, $this->key, $webhookUrlWithParams);

        return $webhookUrlWithParams;
    }



    /**
     * Checks if the Payshop payment is complete/paid.
     *
     * Purpose: This method is used as an alternative to the webhook, in order to verify if a specific Payshop payment has been successfully paid.
     * This can be used if you encounter issues with webhooks or need to double-check the payment status for a specific transaction.
     *
     * @param Payshop $payment The Payshop payment object containing transaction details.
     * @return bool Returns true if the payment is complete, false otherwise.
     * @throws EndpointResponseException If the API response indicates an error or unexpected content.
     */
    public function isPaid(Payshop $payment): bool
    {
        return $this->paymentService->isPaid($payment);
    }



    /**
     * Checks if the payment has expired.
     *
     * @param Payshop $payment Payment instance to check.
     * @return bool Returns true if the payment is expired, false if not expired or if expiration is not set.
     */
    public function isExpired(Payshop $payment): bool
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
        // NOTE: Payshop expects a Ymd date, but it expires at the start of the day
        // so we add one day to the expiration to make it expire at the end of the day
        // and be consistent with other payment methods
        return DateTools::getFutureDate($daysToExpire + 1, 23, 59, true);
    }
}
