<?php

namespace Ifthenpay\PaymentGateway\Interface\Service;

use Ifthenpay\PaymentGateway\Enums\Status;
use Ifthenpay\PaymentGateway\Model\Cofidis;
use Ifthenpay\PaymentGateway\Model\CofidisCustomerData;
use Ifthenpay\PaymentGateway\RequestObj\WebhookRequest;

interface CofidisServiceInterface
{
    public function initPayment(string $orderId, string $amount, CofidisCustomerData $customerData, ?string $description = null, ?string $returnUrl = null): Cofidis;
    public function isPaid(Cofidis $cofidisPayment): Bool;
    public function getPaymentStatus(string $transactionId, int $numberOfAttempts = 3): Status;
    public function registerWebhook(string $webhookUrl, ?array $extraParams = null): string;
    public function validateWebhook(WebhookRequest $webhookRequest, Cofidis $cofidisPayment): void;
    public function isExpired(Cofidis $payment): bool;
}
