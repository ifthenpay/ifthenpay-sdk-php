<?php

namespace Ifthenpay\PaymentGateway\Service;

use Ifthenpay\PaymentGateway\Config;
use Ifthenpay\PaymentGateway\Enums\Status;
use Ifthenpay\PaymentGateway\Enums\MethodCode;
use Ifthenpay\PaymentGateway\Exception\WebhookValidationException;
use Ifthenpay\PaymentGateway\Exception\EndpointResponseException;
use Ifthenpay\PaymentGateway\Interface\Service\CofidisServiceInterface;
use Ifthenpay\PaymentGateway\Model\Cofidis;
use Ifthenpay\PaymentGateway\Model\CofidisCustomerData;
use Ifthenpay\PaymentGateway\RequestObj\CofidisInitRequest;
use Ifthenpay\PaymentGateway\RequestObj\WebhookRequest;
use Ifthenpay\PaymentGateway\Utils\DateTools;
use Ifthenpay\PaymentGateway\Utils\StringTools;

class CofidisService implements CofidisServiceInterface
{
    public const INIT_STATUS_SUCCESS = '0';
    public const INIT_STATUS_ERROR   = '-1';

    public const COFIDIS_STATUS_INITIATED       = 'INITIATED';
    public const COFIDIS_STATUS_PENDING_INVOICE = 'PENDING_INVOICE';
    public const COFIDIS_STATUS_NOT_APPROVED    = 'NOT_APPROVED';
    public const COFIDIS_STATUS_TECHNICAL_ERROR = 'TECHNICAL_ERROR';
    public const COFIDIS_STATUS_CANCELED        = 'CANCELED';

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
        $this->key            = $config->cofidisKey();
        $this->config         = $config;
        $this->apiService     = $apiService;
        $this->webhookService = $webhookService;
        $this->paymentService = $paymentService;
    }



    /**
     * Initializes a Cofidis payment request.
     *
     * @param string $orderId The unique identifier for the order.
     * @param string $amount The payment amount.
     * @param CofidisCustomerData $customerData The customer data required for Cofidis payment.
     * @param string|null $description Payment description.
     * @param string|null $returnUrl URL to redirect after payment; defaults to configured return URL.
     * @return Cofidis Returns a Cofidis payment object if initialization is successful.
     * @throws EndpointResponseException If the response from the Cofidis API is unexpected or indicates an error.
     */

    public function initPayment(string $orderId, string $amount, CofidisCustomerData $customerData, ?string $description = null, ?string $returnUrl = null): Cofidis
    {
        $request = new CofidisInitRequest(
            $this->key,
            $orderId,
            $amount,
            $returnUrl ?? $this->config->cofidisReturnUrl(),
            $description ?? '',
            $customerData
        );
        $responseObj = $this->apiService->initCofidisPayment($request);

        $response = json_decode((string) $responseObj->getBody(), true);

        if (
            !isset($response['status']) || !isset($response['paymentUrl']) || !isset($response['requestId'])
        ) {
            throw new EndpointResponseException('Cofidis payment request returned unexpected response.', ['request' => $request, 'response' => $response]);
        }

        if ($response['status'] === self::INIT_STATUS_SUCCESS) {
            return new Cofidis($amount, $orderId, $response['requestId'], $response['paymentUrl'], Status::PENDING, $this->expireMinutesToDate(), DateTools::getTimeStamp());
        }

        if ($response['status'] === self::INIT_STATUS_ERROR) {
            throw new EndpointResponseException('Error initializing the request.', ['request' => $request, 'response' => $response]);
        }

        throw new EndpointResponseException('Cofidis payment request returned unexpected response.', ['response' => $response]);
    }


    /**
     * Validates the incoming webhook request to ensure its authenticity.
     *
     * Purpose: Used to protect against fraudulent or tampered webhook requests.
     * You are required to convert the parameters received in the webhook to a WebhookRequest object to pass it in the function.
     *
     * @param WebhookRequest $webhookRequest The incoming webhook request data.
     * @param Cofidis $payment The payment that is being compared.
     * @return void
     * @throws WebhookValidationException If the webhook validation fails due to missing parameters or mismatched values.
     */
    public function validateWebhook(WebhookRequest $webhookRequest, Cofidis $payment): void
    {
        $paymentArray = array_merge($payment->toArray(), ['antiPhishingKey' => $this->config->antiPhishingKey()]);

        $this->webhookService->validateWebhook($webhookRequest->toArray(), $paymentArray, ['tid' => 'transactionId']);
    }



    /**
     * Registers a webhook URL for the COFIDIS account set in config.
     *
     * Purpose: This method is used to set up a webhook URL that ifthenpay will call to when a payment occurs for the COFIDIS account set in the config.
     * This is used to update the payment status in real-time without needing to poll the API for status updates, and is the preferred method for tracking payment status.
     * Calling this method will overwrite any previously set webhook URL for the COFIDIS account.
     *
     * @param string $webhookUrl The URL to register as a webhook for COFIDIS callbacks.
     * @param array<string, string>|null $extraParams Optional additional query parameters placeholders to include in the webhook URL.
     * @return string The full webhook URL with query parameters that was registered.
     * @throws EndpointResponseException If the API response is unexpected or registration fails.
     */
    public function registerWebhook(string $webhookUrl, ?array $extraParams = null): string
    {
        $webhookParams        = $extraParams ? array_merge($this->webhookService->getWebhookParams(MethodCode::COFIDIS), $extraParams) : $this->webhookService->getWebhookParams(MethodCode::COFIDIS);
        $webhookUrlWithParams = StringTools::addQueryStringVars($webhookUrl, $webhookParams);

        $this->webhookService->registerWebhook(MethodCode::COFIDIS->value, $this->key, $webhookUrlWithParams);

        return $webhookUrlWithParams;
    }


    /**
     * Checks if the Cofidis payment is complete/paid.
     *
     * Purpose: This method is used as an alternative to the webhook, in order to verify if a specific Cofidis payment has been successfully paid.
     * This can be used if you encounter issues with webhooks or need to double-check the payment status for a specific transaction.
     *
     * @param Cofidis $payment The Cofidis payment object containing transaction details.
     * @return bool Returns true if the payment is complete, false otherwise.
     * @throws EndpointResponseException If the API response indicates an error or unexpected content.
     */
    public function isPaid(Cofidis $payment): bool
    {
        return $this->paymentService->isPaid($payment);
    }



    /**
     * Gets the payment status for a given Cofidis transaction.
     *
     * Attempts to fetch the payment status up to a specified number of times,
     * with increasing delay between attempts, to account for possible delays in status availability.
     * The status code returned by the API is mapped to a corresponding Status enum value.
     *
     * @param string $transactionId The transaction ID.
     * @param int $numberOrAttempts The number of attempts to check the status with a max of 5 attempts.
     * @return Status
     * @throws EndpointResponseException If the API response indicates an error or unexpected content.
     */
    public function getPaymentStatus(string $transactionId, int $numberOrAttempts = 3): Status
    {
        // sometimes the status is not immediately available, so we try a total of 3 times with increasing delay
        $numberOrAttempts = $numberOrAttempts < 5 ? $numberOrAttempts : 5;
        $statusCode       = 'Error';
        for ($i = 0; $i < $numberOrAttempts; $i++) {
            $responseObj = $this->apiService->getCofidisPaymentStatus($this->key, $transactionId);
            $response    = json_decode((string) $responseObj->getBody(), true);

            if (isset($response[0]) && isset($response[0]['statusCode'])) {
                $statusCode = $response[0]['statusCode'];
            }

            if (count($response) > 1) {
                break;
            }

            sleep(1 + $i * 3);
        }

        switch ($statusCode) {
            case self::COFIDIS_STATUS_INITIATED:
                $status = Status::PENDING;
                break;
            case self::COFIDIS_STATUS_PENDING_INVOICE:
                $status = Status::PAID;
                break;
            case self::COFIDIS_STATUS_NOT_APPROVED:
                $status = Status::DECLINED;
                break;
            case self::COFIDIS_STATUS_TECHNICAL_ERROR:
                $status = Status::ERROR;
                break;
            case self::COFIDIS_STATUS_CANCELED:
                $status = Status::CANCELED;
                break;
            default:
                throw new EndpointResponseException('Cofidis get payment status returned unexpected status code.', ['transaction' => $transactionId, 'status' => $statusCode]);
        }

        return $status;
    }



    /**
     * Checks if the payment has expired.
     *
     * @param Cofidis $payment Payment instance to check.
     * @return bool Returns true if the payment is expired, false if not expired or if expiration is not set.
     */
    public function isExpired(Cofidis $payment): bool
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
        $minutesToExpire = $this->config->cofidisMinutesToExpire();
        if ($minutesToExpire === null) {
            return null;
        }
        return DateTools::getFutureDate(0, 0, $minutesToExpire);
    }
}
