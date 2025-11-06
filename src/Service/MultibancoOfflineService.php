<?php

declare(strict_types=1);

namespace Ifthenpay\PaymentGateway\Service;

use Ifthenpay\PaymentGateway\Config;
use Ifthenpay\PaymentGateway\Enums\MethodCode;
use Ifthenpay\PaymentGateway\Enums\Status;
use Ifthenpay\PaymentGateway\Exception\EndpointResponseException;
use Ifthenpay\PaymentGateway\Exception\IfthenpayException;
use Ifthenpay\PaymentGateway\Exception\WebhookValidationException;
use Ifthenpay\PaymentGateway\Interface\Service\MultibancoOfflineServiceInterface;
use Ifthenpay\PaymentGateway\Model\MultibancoOffline;
use Ifthenpay\PaymentGateway\RequestObj\MultibancoOfflineRequest;
use Ifthenpay\PaymentGateway\RequestObj\WebhookRequest;
use Ifthenpay\PaymentGateway\Utils\DateTools;
use Ifthenpay\PaymentGateway\Utils\StringTools;

class MultibancoOfflineService implements MultibancoOfflineServiceInterface
{
    public const INIT_STATUS_SUCCESS = '0'; // Request initialized successfully (pending acceptance).
    public const INIT_STATUS_ERROR   = '-1'; // Error initializing the request.

    private string $entity;
    private string $subEntity;
    private Config $config;
    private WebhookService $webhookService;
    private PaymentService $paymentService;

    public function __construct(
        Config $config,
        WebhookService $webhookService,
        PaymentService $paymentService
    ) {
        $this->entity         = $config->multibancoOfflineEntity();
        $this->subEntity      = $config->multibancoOfflineSubEntity();
        $this->config         = $config;
        $this->webhookService = $webhookService;
        $this->paymentService = $paymentService;
    }



    /**
     * Initializes a Multibanco offline payment.
     *
     * Generates an offline reference, and returns a MultibancoOffline instance.
     *
     * @param string $orderId Unique identifier for the order.
     * @param string $amount Payment amount for the order.
     * @return MultibancoOffline The initialized Multibanco offline payment object.
     * @throws IfthenpayException If an error occurs while generating the offline reference.
     */
    public function initPayment(string $orderId, string $amount): MultibancoOffline
    {
        $request = new MultibancoOfflineRequest(
            $this->entity,
            $this->subEntity,
            $orderId,
            $amount
        );

        try {
            $reference = $this->generateOfflineReference($request->orderId, $request->amount, $request->entity, $request->subEntity);

            return new MultibancoOffline($amount, $orderId, $this->entity, $reference, Status::PENDING, $this->expireDaysToDate(), DateTools::getTimeStamp());
        } catch (\Exception $th) {
            throw new IfthenpayException('Error generating offline Multibanco reference.', ['orderId' => $orderId, 'amount' => $amount, 'entity' => $this->entity, 'subEntity' => $this->subEntity], 0, $th);
        }
    }


    /**
     * Generates a offline Multibanco reference number based on the provided parameters.
     * Purpose: This method is used to generate a offline Multibanco reference without relying on the api.
     *
     * @param string $orderId A unique identifier for the order (max 4 digits, remainder is truncated), can use 5 digits is subentity only has 2 digits.
     * @param string $amountStr The amount to be paid, formatted as a string with two decimal places (e.g., "10.00").
     * @param string $entity The entity number provided by Multibanco (usually 5 digits).
     * @param string $subEntity The sub-entity number provided by Multibanco (usually 2 or 3 digits), 2 digits if you want to pass 5 digits in the orderId
     * @return string The generated offline Multibanco reference number.
     */
    private function generateOfflineReference(string $orderId, string $amountStr, string $entity, string $subEntity): string
    {
        $amount  = (int) $amountStr;
        $orderId = "0000" . $orderId;

        if (strlen($subEntity) === 2) {
            // only the 5 rightmost characters of order_id are considered
            $seed    = substr($orderId, (strlen($orderId) - 5), strlen($orderId));
            $chk_str = sprintf('%05u%02u%05u%08u', $entity, $subEntity, $seed, round($amount * 100));
        } else {
            // only the 4 rightmost characters of order_id are considered
            $seed    = substr($orderId, (strlen($orderId) - 4), strlen($orderId));
            $chk_str = sprintf('%05u%03u%04u%08u', $entity, $subEntity, $seed, round($amount * 100));
        }
        $chk_array = [3, 30, 9, 90, 27, 76, 81, 34, 49, 5, 50, 15, 53, 45, 62, 38, 89, 17, 73, 51];
        $chk_val   = 0;
        for ($i = 0; $i < 20; $i++) {
            $chk_int = (int)substr($chk_str, 19 - $i, 1);
            $chk_val += ($chk_int % 10) * $chk_array[$i];
        }
        $chk_val %= 97;
        $chk_digits = sprintf('%02u', 98 - $chk_val);

        return $subEntity . $seed . $chk_digits;
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
        $webhookParams        = $extraParams ? array_merge($this->webhookService->getWebhookParams(MethodCode::MULTIBANCO_OFFLINE), $extraParams) : $this->webhookService->getWebhookParams(MethodCode::MULTIBANCO_OFFLINE);
        $webhookUrlWithParams = StringTools::addQueryStringVars($webhookUrl, $webhookParams);

        $this->webhookService->registerWebhook($this->entity, $this->subEntity, $webhookUrlWithParams);

        return $webhookUrlWithParams;
    }



    /**
     * Validates the incoming webhook request to ensure its authenticity.
     *
     * Purpose: Used to protect against fraudulent or tampered webhook requests.
     * You are required to convert the parameters received in the webhook to a WebhookRequest object to pass it in the function.
     *
     * @param WebhookRequest $webhookRequest The incoming webhook request data.
     * @param MultibancoOffline $payment The payment that is being compared.
     * @return void
     * @throws WebhookValidationException If the webhook validation fails due to missing parameters or mismatched values.
     */
    public function validateWebhook(WebhookRequest $webhookRequest, MultibancoOffline $payment): void
    {
        $paymentArray = array_merge($payment->toArray(), ['antiPhishingKey' => $this->config->antiPhishingKey()]);

        $this->webhookService->validateWebhook($webhookRequest->toArray(), $paymentArray, ['ref' => 'reference']);
    }



    /**
     * Checks if the MultibancoOffline payment is complete/paid.
     *
     * Purpose: This method is used as an alternative to the webhook, in order to verify if a specific MultibancoOffline payment has been successfully paid.
     * This can be used if you encounter issues with webhooks or need to double-check the payment status for a specific transaction.
     *
     * @param MultibancoOffline $payment The MultibancoOffline payment object containing transaction details.
     * @return bool Returns true if the payment is complete, false otherwise.
     * @throws EndpointResponseException If the API response indicates an error or unexpected content.
     */
    public function isPaid(MultibancoOffline $payment): bool
    {
        return $this->paymentService->isPaid($payment);
    }

    /**
     * Checks if the payment has expired.
     *
     * @param MultibancoOffline $payment Payment instance to check.
     * @return bool Returns true if the payment is expired, false if not expired or if expiration is not set.
     */
    public function isExpired(MultibancoOffline $payment): bool
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
    private function expireDaysToDate(): ?\DateTimeImmutable
    {
        $daysToExpire = $this->config->multibancoOfflineDaysToExpire();

        if ($daysToExpire === null) {
            return null;
        }
        return DateTools::getFutureDate($daysToExpire, 23, 59, true);
    }
}
