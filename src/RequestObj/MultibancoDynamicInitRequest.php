<?php

namespace Ifthenpay\PaymentGateway\RequestObj;

use Ifthenpay\PaymentGateway\Utils\Validation;

class MultibancoDynamicInitRequest
{
    public string $multibancoKey;
    public string $orderId;
    public string $amount;
    public ?string $description = null;
    public ?int $daysToExpire   = null;


    public function __construct(
        string $multibancoKey,
        string $orderId,
        string $amount,
        ?string $description = null,
        ?int $daysToExpire = null
    ) {

        $this->multibancoKey = $multibancoKey;
        $this->orderId       = $orderId;
        $this->amount        = $amount;
        $this->description   = $description;
        $this->daysToExpire  = $daysToExpire;

        Validation::validate(
            [
                'multibancoKey' => $this->multibancoKey,
                'orderId'       => $this->orderId,
                'amount'        => $this->amount,
                'description'   => $this->description,
                'daysToExpire'  => $this->daysToExpire,
            ],
            [
                'multibancoKey' => ['required', 'regex_key'],
                'orderId'       => ['required', 'max_len:25'],
                'amount'        => ['required', 'regex_money', 'max_len:10'],
                'description'   => ['nullable', 'max_len:255'],
                'daysToExpire'  => ['nullable', 'regex_mb_expire_days'],
            ]
        );
    }


    /**
     * Converts the MultibancoDynamicInitRequest object to an associative array.
     * @return array<string, int|string|null>
     */
    public function toPayload(): array
    {
        return [
            "mbKey"       => $this->multibancoKey,
            "orderId"     => $this->orderId,
            "amount"      => $this->amount,
            "description" => $this->description,
            "expiryDays"  => $this->daysToExpire
        ];
    }
}
