<?php

namespace Ifthenpay\PaymentGateway\RequestObj;

use Ifthenpay\PaymentGateway\Utils\Validation;

class MbwayInitRequest
{
    public function __construct(
        public string $mbwayKey,
        public string $orderId,
        public string $amount,
        public string $mobileNumber,
        public ?string $description = null,
        public ?string $email = null
    ) {
        Validation::validate(
            [
                'mbwayKey'     => $this->mbwayKey,
                'orderId'      => $this->orderId,
                'amount'       => $this->amount,
                'mobileNumber' => $this->mobileNumber,
                'description'  => $this->description,
                'email'        => $this->email,
            ],
            [
                'mbwayKey'     => ['required', 'regex_key'],
                'orderId'      => ['required', 'max_len:25'],
                'amount'       => ['required', 'regex_money', 'max_len:10'],
                'mobileNumber' => ['required', 'regex_mobile'],
                'description'  => ['nullable', 'max_len:100'],
                'email'        => ['nullable', 'email', 'max_len:100'],
            ]
        );
    }


    /**
     * Converts the MbwayInitRequest object to an associative array.
     * @return array<string, string|null>
     */
    public function toPayload(): array
    {
        return [
            "mbWayKey"     => $this->mbwayKey,
            "orderId"      => $this->orderId,
            "amount"       => $this->amount,
            "mobileNumber" => $this->mobileNumber,
            "email"        => $this->email,
            "description"  => $this->description
        ];
    }
}
