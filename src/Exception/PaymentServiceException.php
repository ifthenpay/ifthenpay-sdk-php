<?php

namespace Ifthenpay\PaymentGateway\Exception;

class PaymentServiceException extends IfthenpayException
{
    protected $message = 'Unexpected error in PaymentService.';


    public function __construct(string $message, array $data = [], int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $data, $code, $previous);
    }
}
