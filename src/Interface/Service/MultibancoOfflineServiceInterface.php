<?php

namespace Ifthenpay\PaymentGateway\Interface\Service;

use Ifthenpay\PaymentGateway\Model\MultibancoOffline;
use Ifthenpay\PaymentGateway\RequestObj\WebhookRequest;

interface MultibancoOfflineServiceInterface
{
    public function initPayment(string $orderId, string $amount): MultibancoOffline;
    public function isPaid(MultibancoOffline $multibancoPayment): Bool;
    public function registerWebhook(string $webhookUrl, ?array $extraParams = null): string;
    public function validateWebhook(WebhookRequest $webhookRequest, MultibancoOffline $multibancoPayment): void;
    public function isExpired(MultibancoOffline $payment): bool;
}
