<?php

namespace Ifthenpay\PaymentGateway\Interface\Service;

use Ifthenpay\PaymentGateway\Enums\MethodCode;
use Ifthenpay\PaymentGateway\Model\PayByLink;
use Ifthenpay\PaymentGateway\RequestObj\WebhookRequest;

interface PayByLinkServiceInterface
{
    public function initPayment(string $orderId, string $amount, string $description, string $successUrl, string $errorUrl, string $cancelUrl, string $returnUrl, string $language = 'pt'): PayByLink;
    public function registerWebhook(string $webhookUrl, ?array $extraParams = null): string;
    public function validateWebhook(WebhookRequest $webhookRequest, PayByLink $payByLinkPayment): void;
    public function isTransactionPaid(string $transactionId): bool|MethodCode;
    public function isExpired(PayByLink $payment): bool;
}
