<?php

namespace Ifthenpay\PaymentGateway\Exception;

class WebhookValidationException extends IfthenpayException
{
    protected $message = 'Invalid webhook.';

    /**
     * Webhook validation exception constructor.
     * @param string $message
     * @param array<string, mixed> $data
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(string $message, array $data = [], int $code = 0, ?\Throwable $previous = null)
    {
        $this->data = $data;
        parent::__construct($message, $data, $code, $previous);
    }
}
