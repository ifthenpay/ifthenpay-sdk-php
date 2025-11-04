<?php

namespace Ifthenpay\PaymentGateway\Interface\Service;

use Ifthenpay\PaymentGateway\Enums\Status;
use Ifthenpay\PaymentGateway\Model\Mbway;
use Ifthenpay\PaymentGateway\RequestObj\WebhookRequest;

interface MbwayServiceInterface
{
    public function initPayment(string $orderId, string $amount, string $mobileNumber, ?string $description = null, ?string $email = null): Mbway;
    public function isPaid(Mbway $mbwayPayment): Bool;
    public function getPaymentStatus(string $transactionId): Status;
    public function registerWebhook(string $webhookUrl, ?array $extraParams = null): string;
    public function validateWebhook(WebhookRequest $webhookRequest, Mbway $mbwayPayment): void;
    public function isExpired(Mbway $payment): bool;
}
