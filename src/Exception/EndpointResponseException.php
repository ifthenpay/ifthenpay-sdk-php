<?php

namespace Ifthenpay\PaymentGateway\Exception;

class EndpointResponseException extends IfthenpayException
{
    protected $message = 'Invalid or unusable response from endpoint.';


    /**
     * Endpoint response exception constructor.
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
