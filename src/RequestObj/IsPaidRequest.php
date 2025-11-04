<?php

namespace Ifthenpay\PaymentGateway\RequestObj;

use Ifthenpay\PaymentGateway\Utils\Validation;

class IsPaidRequest
{
    public function __construct(
        public string $backofficeKey,
        public ?string $reference = null,
        public ?string $transactionId = null,
        public ?string $amount = null,
        public ?string $orderId = null,
        public ?string $dateStart = null,
        public ?string $dateEnd = null
    ) {
        Validation::validate(
            [
                'backofficeKey' => $this->backofficeKey,
                'reference'     => $this->reference,
                'transactionId' => $this->transactionId,
                'amount'        => $this->amount,
                'orderId'       => $this->orderId,
                'dateStart'     => $this->dateStart,
                'dateEnd'       => $this->dateEnd,
            ],
            [
                'backofficeKey' => ['required', 'regex_bokey'],
                'reference'     => ['nullable', 'max_len:20'],
                'transactionId' => ['nullable', 'max_len:20'],
                'amount'        => ['nullable', 'regex_money', 'max_len:10'],
                'orderId'       => ['nullable', 'max_len:25'],
                'dateStart'     => ['nullable', 'regex_date:d-m-Y H:i:s'],
                'dateEnd'       => ['nullable', 'regex_date:d-m-Y H:i'],
            ]
        );
    }


    /**
     * Converts the IsPaidRequest object to an associative array.
     * @return array<string, string|null>
     */
    public function toPayload(): array
    {
        return [
            "boKey"     => $this->backofficeKey,
            "reference" => $this->reference,
            "requestId" => $this->transactionId,
            "amount"    => $this->amount,
            "orderId"   => $this->orderId,
            "dateStart" => $this->dateStart,
            "dateEnd"   => $this->dateEnd,
        ];
    }
}
