<?php

namespace Ifthenpay\PaymentGateway\Exception;

class IfthenpayException extends \Exception
{
    /** @var array<string, mixed> */
    protected array $data;


    /**
     * Ifthenpay exception constructor.
     * @param string $message
     * @param array<string, mixed> $data
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(string $message, array $data = [], int $code = 0, ?\Throwable $previous = null)
    {
        $this->data = $data;
        parent::__construct($message, $code, $previous);
    }


    /**
     * Get additional data associated with the exception.
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        return $this->data;
    }
}
