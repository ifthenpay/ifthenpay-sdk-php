<?php

namespace Ifthenpay\PaymentGateway\Service;

use Ifthenpay\PaymentGateway\Config;
use Ifthenpay\PaymentGateway\Enums\Status;
use Ifthenpay\PaymentGateway\Enums\MethodCode;
use Ifthenpay\PaymentGateway\Exception\WebhookValidationException;
use Ifthenpay\PaymentGateway\Exception\EndpointResponseException;
use Ifthenpay\PaymentGateway\Interface\Service\PixServiceInterface;
use Ifthenpay\PaymentGateway\Model\Pix;
use Ifthenpay\PaymentGateway\RequestObj\PixInitRequest;
use Ifthenpay\PaymentGateway\RequestObj\WebhookRequest;
use Ifthenpay\PaymentGateway\Utils\DateTools;
use Ifthenpay\PaymentGateway\Utils\StringTools;

class PixService implements PixServiceInterface
{
    public const INIT_STATUS_SUCCESS = '0'; // Request initialized successfully (pending acceptance).
    public const INIT_STATUS_ERROR   = '-1'; // Error initializing the request. You can try again.

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
        $this->key            = $config->pixKey();
        $this->config         = $config;
        $this->apiService     = $apiService;
        $this->webhookService = $webhookService;
        $this->paymentService = $paymentService;
    }



    /**
     * Initializes a PIX payment request.
     * Purpose: This method is used to create a new PIX payment request which generates a unique transaction ID that can be used to track the payment status.
     * A notification is sent to the user's PIX app to approve the payment (the logic for this is handled by SIBS).
     *
     * @param string $orderId A unique identifier for the order.
     * @param string $amount The amount to be paid, formatted as a string with two decimal places (e.g., "10.00").
     * @param string $cpf number of the customer (format example: "111.111.111-11").
     * @param string $name The name of the customer.
     * @param string $email The email address of the customer.
     * @param string $mobileNumber The mobile number associated with the PIX account (format example: "912345678", "351#912345678").
     * @param string $redirect The URL to which the user will be redirected after completing the payment.
     * @param string|null $description An optional description for the payment.
     *
     * @return Pix
     * @throws EndpointResponseException When the gateway explicitly returns an error, incomplete, or declined status, or an unexpected error occurred.
     */
    public function initPayment(string $orderId, string $amount, string $cpf, string $name, string $email, string $mobileNumber, string $redirect, ?string $description = null): Pix
    {
        $request = new PixInitRequest(
            $this->key,
            $orderId,
            $amount,
            $cpf,
            $name,
            $email,
            $mobileNumber,
            $redirect,
            $description
        );
        $responseObj = $this->apiService->initPixPayment($request);

        $response = json_decode((string) $responseObj->getBody(), true);

        if (
            !isset($response['status']) || !isset($response['paymentUrl']) || !isset($response['qrCodeValue']) || !isset($response['requestId'])
        ) {
            throw new EndpointResponseException('Pix payment request returned unexpected response.', ['request' => $request, 'response' => $response]);
        }

        if ($response['status'] === self::INIT_STATUS_SUCCESS) {
            return new Pix($amount, $orderId, $response['requestId'], $mobileNumber, $email, $response['paymentUrl'], $response['qrCodeValue'], Status::PENDING, $this->expireMinutesToDate(), DateTools::getTimeStamp());
        }

        if ($response['status'] === self::INIT_STATUS_ERROR) {
            throw new EndpointResponseException('Error initializing the request.', ['request' => $request, 'response' => $response]);
        }

        throw new EndpointResponseException('Pix payment request returned an error response.', ['response' => $response]);
    }


    /**
     * Validates the incoming webhook request to ensure its authenticity.
     *
     * Purpose: Used to protect against fraudulent or tampered webhook requests.
     * You are required to convert the parameters received in the webhook to a WebhookRequest object to pass it in the function.
     *
     * @param WebhookRequest $webhookRequest The incoming webhook request data.
     * @param Pix $payment The payment that is being compared.
     * @return void
     * @throws WebhookValidationException If the webhook validation fails due to missing parameters or mismatched values.
     */
    public function validateWebhook(WebhookRequest $webhookRequest, Pix $payment): void
    {
        $paymentArray = array_merge($payment->toArray(), ['antiPhishingKey' => $this->config->antiPhishingKey()]);

        $this->webhookService->validateWebhook($webhookRequest->toArray(), $paymentArray, ['tid' => 'transactionId']);
    }



    /**
     * Registers a webhook URL for the PIX account set in config.
     *
     * Purpose: This method is used to set up a webhook URL that ifthenpay will call to when a payment occurs for the PIX account set in the config.
     * This is used to update the payment status in real-time without needing to poll the API for status updates, and is the preferred method for tracking payment status.
     * Calling this method will overwrite any previously set webhook URL for the PIX account.
     *
     * @param string $webhookUrl The URL to register as a webhook for PIX callbacks.
     * @param array<string, string>|null $extraParams Optional additional query parameters placeholders to include in the webhook URL.
     * @return string The full webhook URL with query parameters that was registered.
     * @throws EndpointResponseException If the API response is unexpected or registration fails.
     */
    public function registerWebhook(string $webhookUrl, ?array $extraParams = null): string
    {
        $webhookParams        = $extraParams ? array_merge($this->webhookService->getWebhookParams(MethodCode::PIX), $extraParams) : $this->webhookService->getWebhookParams(MethodCode::PIX);
        $webhookUrlWithParams = StringTools::addQueryStringVars($webhookUrl, $webhookParams);

        $this->webhookService->registerWebhook(MethodCode::PIX->value, $this->key, $webhookUrlWithParams);

        return $webhookUrlWithParams;
    }




    /**
     * Checks if the Pix payment is complete/paid.
     *
     * Purpose: This method is used as an alternative to the webhook, in order to verify if a specific Pix payment has been successfully paid.
     * This can be used if you encounter issues with webhooks or need to double-check the payment status for a specific transaction.
     *
     * @param Pix $payment The Pix payment object containing transaction details.
     * @return bool Returns true if the payment is complete, false otherwise.
     * @throws EndpointResponseException If the API response indicates an error or unexpected content.
     */
    public function isPaid(Pix $payment): bool
    {
        return $this->paymentService->isPaid($payment);
    }



    /**
     * Checks if the payment has expired.
     *
     * @param Pix $payment Payment instance to check.
     * @return bool Returns true if the payment is expired, false if not expired or if expiration is not set.
     */
    public function isExpired(Pix $payment): bool
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
        $minutesToExpire = $this->config->pixMinutesToExpire();
        if ($minutesToExpire === null) {
            return null;
        }
        return DateTools::getFutureDate(0, 0, $minutesToExpire);
    }
}
