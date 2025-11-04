<?php

namespace Ifthenpay\PaymentGateway\Exception;

class WebhookServiceException extends IfthenpayException
{
    protected $message = 'Unexpected error in WebhookService.';

    /**
     * webhook service exception constructor.
     * @param string $message
     * @param array<string, mixed> $data
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(string $message, array $data = [], int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $data, $code, $previous);
    }
}
