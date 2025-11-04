<?php

namespace Ifthenpay\PaymentGateway\Interface\Service;

use Ifthenpay\PaymentGateway\Model\Payshop;
use Ifthenpay\PaymentGateway\RequestObj\WebhookRequest;

interface PayshopServiceInterface
{
    public function initPayment(string $orderId, string $amount, ?int $daysToExpire = null): Payshop;
    public function isPaid(Payshop $payshopPayment): Bool;
    public function registerWebhook(string $webhookUrl, ?array $extraParams = null): string;
    public function validateWebhook(WebhookRequest $webhookRequest, Payshop $payshopPayment): void;
    public function isExpired(Payshop $payment): bool;
}
