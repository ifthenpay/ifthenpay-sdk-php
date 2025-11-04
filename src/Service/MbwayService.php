<?php

namespace Ifthenpay\PaymentGateway\Service;

use Ifthenpay\PaymentGateway\Config;
use Ifthenpay\PaymentGateway\Enums\Status;
use Ifthenpay\PaymentGateway\Enums\MethodCode;
use Ifthenpay\PaymentGateway\Exception\WebhookValidationException;
use Ifthenpay\PaymentGateway\Exception\EndpointResponseException;
use Ifthenpay\PaymentGateway\Interface\Service\MbwayServiceInterface;
use Ifthenpay\PaymentGateway\Model\Mbway;
use Ifthenpay\PaymentGateway\RequestObj\MbwayInitRequest;
use Ifthenpay\PaymentGateway\RequestObj\WebhookRequest;
use Ifthenpay\PaymentGateway\Utils\DateTools;
use Ifthenpay\PaymentGateway\Utils\StringTools;

class MbwayService implements MbwayServiceInterface
{
    public const INIT_STATUS_SUCCESS         = '000'; // Request initialized successfully (pending acceptance).
    public const INIT_STATUS_ERROR           = '999'; // Error initializing the request. You can try again.
    public const INIT_STATUS_INCOMPLETE      = '100'; // The initialization request could not be completed. You can try again.
    public const INIT_STATUS_DECLINED        = '122'; // Transaction declined by SIBS to the user.
    public const INIT_STATUS_INVALID_ACCOUNT = '-1'; // The MB WAY key is invalid.

    public const STATUS_PENDING          = '123'; // Transaction pending payment,
    public const STATUS_PAID             = '000'; // Transaction successfully completed (Payment confirmed),
    public const STATUS_REJECTED_BY_USER = '020'; // Transaction rejected by the user.
    public const STATUS_EXPIRED          = '101'; // Transaction expired (the user has 4 minutes to accept the payment in the MB WAY App before expiring).
    public const STATUS_DECLINED         = '122'; // Transaction declined to the user.

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
        $this->key            = $config->mbwayKey();
        $this->config         = $config;
        $this->apiService     = $apiService;
        $this->webhookService = $webhookService;
        $this->paymentService = $paymentService;
    }



    /**
     * Initializes a MB WAY payment request.
     * Purpose: This method is used to create a new MB WAY payment request which generates a unique transaction ID that can be used to track the payment status.
     * A notification is sent to the user's MB WAY app to approve the payment (the logic for this is handled by SIBS).
     *
     * @param string $orderId A unique identifier for the order (max 15 characters).
     * @param string $amount The amount to be paid, formatted as a string with two decimal places (e.g., "10.00").
     * @param string $mobileNumber The mobile number associated with the MB WAY account (format example: "912345678", "351#912345678").
     * @param string|null $description An optional description for the payment (max 100 characters).
     * @param string|null $email Optional email address of the client/customer buying the order, this is not required by MB WAY but can be useful for your own records (max 100 characters).
     *
     * @return Mbway
     * @throws EndpointResponseException When the gateway explicitly returns an error, incomplete, or declined status, or an unexpected error occurred.
     */
    public function initPayment(string $orderId, string $amount, string $mobileNumber, ?string $description = null, ?string $email = null): Mbway
    {
        $request = new MbwayInitRequest(
            $this->key,
            $orderId,
            $amount,
            $mobileNumber,
            $description,
            $email
        );
        $responseObj = $this->apiService->initMbwayPayment($request);

        $response = json_decode((string) $responseObj->getBody(), true);

        if (
            !isset($response['Status']) || !isset($response['Amount']) || !isset($response['OrderId']) || !isset($response['RequestId'])
        ) {
            throw new EndpointResponseException('Mbway payment request returned unexpected response.', ['request' => $request, 'response' => $response]);
        }

        if ($response['Status'] === self::INIT_STATUS_SUCCESS) {

            return new Mbway($response['Amount'], $response['OrderId'], $response['RequestId'], $mobileNumber, Status::PENDING, $this->expireMinutesToDate(), DateTools::getTimeStamp());
        }

        if ($response['Status'] === self::INIT_STATUS_ERROR) {
            throw new EndpointResponseException('Error initializing the request.', ['request' => $request, 'response' => $response]);
        }

        if ($response['Status'] === self::INIT_STATUS_INCOMPLETE) {
            throw new EndpointResponseException('The initialization request could not be completed.', ['request' => $request, 'response' => $response]);
        }

        if ($response['Status'] === self::INIT_STATUS_DECLINED) {
            throw new EndpointResponseException('Transaction declined by SIBS to the user.', ['request' => $request, 'response' => $response]);
        }

        if ($response['Status'] === self::INIT_STATUS_INVALID_ACCOUNT) {
            throw new EndpointResponseException('The MB WAY key is invalid.', ['request' => $request, 'response' => $response]);
        }

        throw new EndpointResponseException('Mbway payment request returned unexpected response.', ['request' => $request, 'response' => $response]);
    }


    /**
     * Validates the incoming webhook request to ensure its authenticity.
     *
     * Purpose: Used to protect against fraudulent or tampered webhook requests.
     * You are required to convert the parameters received in the webhook to a WebhookRequest object to pass it in the function.
     *
     * @param WebhookRequest $webhookRequest The incoming webhook request data.
     * @param Mbway $payment The payment that is being compared.
     * @return void
     * @throws WebhookValidationException If the webhook validation fails due to missing parameters or mismatched values.
     */
    public function validateWebhook(WebhookRequest $webhookRequest, Mbway $payment): void
    {
        $paymentArray = array_merge($payment->toArray(), ['antiPhishingKey' => $this->config->antiPhishingKey()]);

        $this->webhookService->validateWebhook($webhookRequest->toArray(), $paymentArray, ['tid' => 'transactionId']);
    }



    /**
     * Retrieves the payment status for a given MBWay transaction ID.
     *
     * Purpose: Used to check the current status of a MBWay payment which can then be used to inform the user of the payment state.
     * This can be useful when displaying a countdown timer (normaly 4 minutes) for the user to complete the payment in the MBWay app.
     *
     * @param string $transactionId The unique identifier for the MBWay payment request.
     * @return Status The payment status as a Status enum value.
     * @throws EndpointResponseException If the response is invalid or contains an unexpected status code.
     */
    public function getPaymentStatus(string $transactionId): Status
    {
        $responseObj = $this->apiService->getMbwayPaymentStatus($this->key, $transactionId);

        $response = json_decode((string) $responseObj->getBody(), true);

        if (!isset($response['Status'])) {
            throw new EndpointResponseException('Mbway get payment status returned unexpected response.', ['response' => $response]);
        }

        switch ($response['Status']) {
            case self::STATUS_PENDING:

                if ($response['Message'] === 'Request not found') {
                    throw new EndpointResponseException('MB WAY transaction Id not found.', ['response' => $response]);
                }

                $status = Status::PENDING;
                break;
            case self::STATUS_PAID:
                $status = Status::PAID;
                break;
            case self::STATUS_REJECTED_BY_USER:
                $status = Status::REJECTED_BY_USER;
                break;
            case self::STATUS_EXPIRED:
                $status = Status::EXPIRED;
                break;
            case self::STATUS_DECLINED:
                $status = Status::DECLINED;
                break;
            default:
                throw new EndpointResponseException('Mbway get payment status returned unexpected status code.', ['response' => $response]);
        }

        return $status;
    }



    /**
     * Registers a webhook URL for the MBWAY account set in config.
     *
     * Purpose: This method is used to set up a webhook URL that ifthenpay will call to when a payment occurs for the MBWAY account set in the config.
     * This is used to update the payment status in real-time without needing to poll the API for status updates, and is the preferred method for tracking payment status.
     * Calling this method will overwrite any previously set webhook URL for the MBWAY account.
     *
     * @param string $webhookUrl The URL to register as a webhook for MBWAY callbacks.
     * @param array<string, string>|null $extraParams Optional additional query parameters placeholders to include in the webhook URL.
     * @return string The full webhook URL with query parameters that was registered.
     * @throws EndpointResponseException If the API response is unexpected or registration fails.
     */
    public function registerWebhook(string $webhookUrl, ?array $extraParams = null): string
    {
        $webhookParams        = $extraParams ? array_merge($this->webhookService->getWebhookParams(MethodCode::MBWAY), $extraParams) : $this->webhookService->getWebhookParams(MethodCode::MBWAY);
        $webhookUrlWithParams = StringTools::addQueryStringVars($webhookUrl, $webhookParams);

        $this->webhookService->registerWebhook(MethodCode::MBWAY->value, $this->key, $webhookUrlWithParams);

        return $webhookUrlWithParams;
    }



    /**
     * Checks if the MBWay payment is complete/paid.
     *
     * Purpose: This method is used as an alternative to the webhook, in order to verify if a specific MBWay payment has been successfully paid.
     * This can be used if you encounter issues with webhooks or need to double-check the payment status for a specific transaction.
     *
     * @param Mbway $payment The MBWay payment object containing transaction details.
     * @return bool Returns true if the payment is complete, false otherwise.
     * @throws EndpointResponseException If the API response indicates an error or unexpected content.
     */
    public function isPaid(Mbway $payment): bool
    {
        return $this->paymentService->isPaid($payment);
    }



    /**
     * Checks if the payment has expired.
     *
     * @param Mbway $payment Payment instance to check.
     * @return bool Returns true if the payment is expired, false if not expired or if expiration is not set.
     */
    public function isExpired(Mbway $payment): bool
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
        $minutesToExpire = $this->config->mbwayMinutesToExpire();
        if ($minutesToExpire === null) {
            return null;
        }
        return DateTools::getFutureDate(0, 0, $minutesToExpire);
    }
}
