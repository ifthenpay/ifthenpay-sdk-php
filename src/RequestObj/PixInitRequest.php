<?php

namespace Ifthenpay\PaymentGateway\RequestObj;

use Ifthenpay\PaymentGateway\Utils\Validation;

class PixInitRequest
{
    public function __construct(
        public string $pixKey,
        public string $orderId,
        public string $amount,
        public string $cpf,
        public string $name,
        public string $email,
        public string $mobileNumber,
        public string $redirectUrl,
        public ?string $description = null,
    ) {
        Validation::validate(
            [
                'pixKey'       => $this->pixKey,
                'orderId'      => $this->orderId,
                'amount'       => $this->amount,
                'cpf'          => $this->cpf,
                'name'         => $this->name,
                'email'        => $this->email,
                'mobileNumber' => $this->mobileNumber,
                'redirectUrl'  => $this->redirectUrl,
                'description'  => $this->description,
            ],
            [
                'pixKey'       => ['required', 'regex_key'],
                'orderId'      => ['required', 'max_len:25'],
                'amount'       => ['required', 'regex_money', 'max_len:10'],
                'cpf'          => ['required', 'regex_cpf'],
                'name'         => ['required', 'max_len:150'],
                'email'        => ['required', 'email', 'max_len:100'],
                'mobileNumber' => ['required', 'regex_mobile'],
                'redirectUrl'  => ['required', 'url', 'max_len:200'],
                'description'  => ['nullable', 'max_len:100'],
            ]
        );
    }


    /**
     * Converts the PixInitRequest object to an associative array.
     * @return array<string, string|null>
     */
    public function toPayload(): array
    {
        return [
            "orderId"       => $this->orderId,
            "amount"        => $this->amount,
            "customerCPF"   => $this->cpf,
            "customerName"  => $this->name,
            "customerEmail" => $this->email,
            "customerPhone" => $this->mobileNumber,
            "redirectUrl"   => $this->redirectUrl,
            "description"   => $this->description
        ];
    }
}
