<?php

namespace Ifthenpay\PaymentGateway\Interface\Service;

use Ifthenpay\PaymentGateway\Model\MultibancoDynamic;
use Ifthenpay\PaymentGateway\RequestObj\WebhookRequest;

interface MultibancoDynamicServiceInterface
{
    public function initPayment(string $orderId, string $amount, ?string $description = null, ?int $daysToExpire = null): MultibancoDynamic;
    public function isPaid(MultibancoDynamic $multibancoPayment): Bool;
    public function registerWebhook(string $webhookUrl, ?array $extraParams = null): string;
    public function validateWebhook(WebhookRequest $webhookRequest, MultibancoDynamic $multibancoPayment): void;
    public function isExpired(MultibancoDynamic $multibancoPayment): bool;
}
