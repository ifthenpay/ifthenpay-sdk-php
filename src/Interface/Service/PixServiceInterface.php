<?php

namespace Ifthenpay\PaymentGateway\Interface\Service;

use Ifthenpay\PaymentGateway\Model\Pix;
use Ifthenpay\PaymentGateway\RequestObj\WebhookRequest;

interface PixServiceInterface
{
    public function initPayment(string $orderId, string $amount, string $cpf, string $name, string $email, string $mobileNumber, string $redirect, ?string $description = null): Pix;
    public function isPaid(Pix $pixPayment): Bool;
    public function registerWebhook(string $webhookUrl, ?array $extraParams = null): string;
    public function validateWebhook(WebhookRequest $webhookRequest, Pix $pixPayment): void;
    public function isExpired(Pix $payment): bool;
}
