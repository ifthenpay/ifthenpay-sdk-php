<?php

namespace Ifthenpay\PaymentGateway\RequestObj;

use Ifthenpay\PaymentGateway\Utils\Validation;

class CreditCardInitRequest
{
    public string $creditCardKey;
    public string $orderId;
    public string $amount;
    public ?string $successUrl;
    public ?string $errorUrl;
    public ?string $cancelUrl;
    public ?string $language;

    public function __construct(
        string $creditCardKey,
        string $orderId,
        string $amount,
        ?string $successUrl,
        ?string $errorUrl,
        ?string $cancelUrl,
        ?string $language
    ) {

        $this->creditCardKey = $creditCardKey;
        $this->orderId       = $orderId;
        $this->amount        = $amount;
        $this->successUrl    = $successUrl;
        $this->errorUrl      = $errorUrl;
        $this->cancelUrl     = $cancelUrl;
        $this->language      = $language;



        Validation::validate(
            [
                'creditCardKey' => $this->creditCardKey,
                'orderId'       => $this->orderId,
                'amount'        => $this->amount,
                'successUrl'    => $this->successUrl,
                'errorUrl'      => $this->errorUrl,
                'cancelUrl'     => $this->cancelUrl,
                'language'      => $this->language,
            ],
            [
                'creditCardKey' => ['required', 'regex_key'],
                'orderId'       => ['required', 'max_len:25'],
                'amount'        => ['required', 'regex_money', 'max_len:10'],
                'successUrl'    => ['nullable', 'url', 'max_len:200'],
                'errorUrl'      => ['nullable', 'url', 'max_len:200'],
                'cancelUrl'     => ['nullable', 'url', 'max_len:200'],
                'language'      => ['nullable', 'enum:Language'],
            ]
        );
    }


    /**
     * Converts the CreditCardInitRequest object to an associative array.
     * @return array<string, string|null>
     */
    public function toPayload(): array
    {
        return [
            "orderId"    => $this->orderId,
            "amount"     => $this->amount,
            "successUrl" => $this->successUrl,
            "errorUrl"   => $this->errorUrl,
            "cancelUrl"  => $this->cancelUrl,
            "language"   => $this->language,
        ];
    }
}
