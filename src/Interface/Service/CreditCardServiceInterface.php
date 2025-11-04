<?php

namespace Ifthenpay\PaymentGateway\Interface\Service;

use Ifthenpay\PaymentGateway\Model\CreditCard;
use Ifthenpay\PaymentGateway\RequestObj\WebhookRequest;

interface CreditCardServiceInterface
{
    public function initPayment(string $orderId, string $amount, string $returnUrl, string $language = 'pt'): CreditCard;
    public function isPaid(CreditCard $creditCardPayment): Bool;
    public function registerWebhook(string $webhookUrl, ?array $extraParams = null): string;
    public function validateWebhook(WebhookRequest $webhookRequest, CreditCard $creditCardPayment): void;
    public function verifyPayment(string $secretKey, CreditCard $payment): void;
    public function isExpired(CreditCard $payment): bool;
}
