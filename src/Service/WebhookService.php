<?php

namespace Ifthenpay\PaymentGateway\Service;

use Ifthenpay\PaymentGateway\Config;
use Ifthenpay\PaymentGateway\Enums\MethodCode;
use Ifthenpay\PaymentGateway\Exception\EndpointResponseException;
use Ifthenpay\PaymentGateway\Exception\WebhookServiceException;
use Ifthenpay\PaymentGateway\Exception\WebhookValidationException;
use Ifthenpay\PaymentGateway\RequestObj\RegisterWebhookRequest;

class WebhookService
{
    public function __construct(
        private Config $config,
        private ApiService $apiService,
    ) {}




    /**
     * Returns an array of webhook parameters based on the provided payment method code.
     *
     * The returned parameters array always includes:
     * - 'oid': Order ID placeholder.
     * - 'tid': Request ID placeholder.
     * - 'val': Amount placeholder.
     * - 'apk': Anti-phishing key placeholder.
     * - 'pm' : Payment method code value.
     *
     * Additional parameters are included depending on the method code:
     * - For PAYSHOP, MULTIBANCO_DYNAMIC, MULTIBANCO_OFFLINE: adds 'ref' (reference placeholder).
     * - For PAY_BY_LINK: adds 'gpm' (payment method placeholder).
     *
     * @param MethodCode $code The payment method code.
     * @return array<string,string> The array of webhook parameters with placeholders.
     * @throws WebhookServiceException If the method code is not supported for webhook parameters.
     */
    public function getWebhookParams(MethodCode $code): array
    {
        $params = [
            'oid' => '[ID]',
            'tid' => '[REQUEST_ID]',
            'val' => '[AMOUNT]',
            'apk' => '[ANTI_PHISHING_KEY]',
            'pm'  => $code->value,
        ];

        switch ($code) {
            case MethodCode::MBWAY:
            case MethodCode::PIX:
            case MethodCode::CREDIT_CARD:
            case MethodCode::COFIDIS:
                return $params;

            case MethodCode::PAYSHOP:
            case MethodCode::MULTIBANCO_DYNAMIC:
            case MethodCode::MULTIBANCO_OFFLINE:
                $params['ref'] = '[REFERENCE]';

                return $params;
            case MethodCode::PAY_BY_LINK:
                $params['gpm'] = '[PAYMENT_METHOD]';
                return $params;

            default:
                throw new WebhookServiceException('Unsupported method code for webhook params', ['methodCode' => $code->value]);
        }
    }



    /**
     * Register a webhook URL for a given payment method and key.
     *
     * @param string $methodCode The payment method code (e.g., 'MBWAY', 'MULTIBANCO', etc.).
     * @param string $key The unique key associated with the payment method.
     * @param string $webhookUrl The URL to which webhook notifications will be sent.
     * @return void
     * @throws EndpointResponseException If the API response indicates an error or unexpected status.
     */
    public function registerWebhook(string $methodCode, string $key, string $webhookUrl): void
    {
        $request = new RegisterWebhookRequest(
            $this->config->backofficeKey(),
            $methodCode,
            $key,
            $this->config->antiPhishingKey(),
            $webhookUrl
        );
        $responseObj = $this->apiService->registerWebhook($request);

        if ($responseObj->getStatusCode() !== 200) {
            throw new EndpointResponseException('Error unexpected response code', ['request' => $request, 'response' => $responseObj->getBody()->getContents()]);
        }

        $responseText = $responseObj->getBody()->getContents();
        if (strpos($responseText, 'OK:') !== 0) {
            throw new EndpointResponseException('Failure to register callback', ['request' => $request, 'response' => $responseObj->getBody()->getContents()]);
        }
    }



    /**
     * Validates the incoming webhook request to ensure its authenticity.
     *
     * Purpose: Used to protect against fraudulent or tampered webhook requests.
     * You are required to convert the parameters received in the webhook to a WebhookRequest object to pass it in the function.
     *
     * @param array<string, string> $webhookRequest The webhook request parameters received.
     * @param array<string, string> $payment The payment details to compare against.
     * @param array<string, string> $additionalParamsToCompare Additional parameters to compare between the webhook and payment.
     * @return void
     * @throws WebhookValidationException If the webhook validation fails due to missing parameters or mismatched values.
     */
    public function validateWebhook(array $webhookRequest, array $payment, array $additionalParamsToCompare = []): void
    {
        // default params to compare
        $defaultParamsToCompare = ['oid' => 'orderId', 'val' => 'amount', 'apk' => 'antiPhishingKey'];
        $paramsToCompare        = array_merge($defaultParamsToCompare, $additionalParamsToCompare);


        foreach ($paramsToCompare as $webhookParamKey => $paymentParamKey) {
            if (!isset($webhookRequest[$webhookParamKey])) {
                throw new WebhookValidationException("Missing webhook parameter " . $webhookParamKey, ['webhookRequest' => $webhookRequest, 'payment' => $payment]);
            }
            if (!isset($payment[$paymentParamKey])) {
                throw new WebhookValidationException("Missing payment parameter " . $paymentParamKey, ['webhookRequest' => $webhookRequest, 'payment' => $payment]);
            }

            if ($webhookRequest[$webhookParamKey] !== $payment[$paymentParamKey]) {
                throw new WebhookValidationException(ucfirst($paymentParamKey) . " does not match", ['webhookRequest' => $webhookRequest, 'payment' => $payment]);
            }
        }
    }
}
