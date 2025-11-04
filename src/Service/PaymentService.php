<?php

namespace Ifthenpay\PaymentGateway\Service;

use Ifthenpay\PaymentGateway\Config;
use Ifthenpay\PaymentGateway\Exception\EndpointResponseException;
use Ifthenpay\PaymentGateway\Exception\PaymentServiceException;
use Ifthenpay\PaymentGateway\Interface\Model\PaymentInterface;
use Ifthenpay\PaymentGateway\RequestObj\IsPaidRequest;
use Ifthenpay\PaymentGateway\Utils\DateTools;

class PaymentService
{
    public function __construct(
        private Config $config,
        private ApiService $apiService,
    ) {
    }



    /**
     * Checks if the given payment has been marked as paid.
     *
     * @param PaymentInterface $payment The payment instance to check, any payment method implementing PaymentInterface can be used.
     * @return bool Returns true if the payment is marked as paid, false otherwise.
     * @throws EndpointResponseException If the API response indicates an error or unexpected content.
     */
    public function isPaid(PaymentInterface $payment): bool
    {
        $request = new IsPaidRequest(
            $this->config->backofficeKey(),
            $payment->getReference(),
            $payment->getTransactionId(),
            $payment->getAmount(),
            $payment->getOrderId(),
            null,
            null
        );

        $responseObj = $this->apiService->isPaid($request);

        $response = json_decode((string) $responseObj->getBody(), true);

        if ($responseObj->getStatusCode() === 403) {
            throw new EndpointResponseException('Error forbiden ' . ($response['message'] ?? ''), ['response' => $response]);
        }

        if ($responseObj->getStatusCode() !== 200) {
            throw new EndpointResponseException('Error unexpected response code', ['response' => $response]);
        }

        if (empty($response['payments'])) {
            return false;
        }

        if (count($response['payments']) > 0) {
            return true;
        }

        throw new EndpointResponseException('Error unexpected response content', ['response' => $response]);
    }



    /**
     * Determines if the given payment has expired.
     *
     * @param PaymentInterface $payment The payment instance to check, any payment method implementing PaymentInterface can be used.
     * @return bool True if the payment is expired, false otherwise.
     * @throws PaymentServiceException If an error occurs while checking expiration.
     */

    public function isExpired(PaymentInterface $payment): bool
    {
        try {

            $expireDate = $payment->getExpireDate();
            if ($expireDate === null) {
                return false;
            }

            return DateTools::isPastDate($expireDate);
        } catch (\Exception $th) {
            throw new PaymentServiceException('Error checking if payment is expired', ['payment' => $payment, 'exception' => $th]);
        }
    }
}
