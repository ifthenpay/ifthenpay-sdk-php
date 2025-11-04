<?php

namespace Ifthenpay\PaymentGateway\RequestObj;

use DateTimeImmutable;
use Ifthenpay\PaymentGateway\Utils\DateTools;
use Ifthenpay\PaymentGateway\Utils\Validation;

class PayshopInitRequest
{
    public function __construct(
        public string $payshopKey,
        public string $orderId,
        public string $amount,
        public ?int $daysToExpire = null
    ) {
        Validation::validate(
            [
                'payshopKey'   => $payshopKey,
                'orderId'      => $orderId,
                'amount'       => $amount,
                'daysToExpire' => $daysToExpire,
            ],
            [
                'payshopKey'   => ['required', 'regex_key'],
                'orderId'      => ['required', 'max_len:25'],
                'amount'       => ['required', 'regex_money', 'max_len:10'],
                'daysToExpire' => ['nullable', 'integer', 'min_val:0', 'max_val:365'],
            ]
        );

        $this->payshopKey   = $payshopKey;
        $this->orderId      = $orderId;
        $this->amount       = $amount;
        $this->daysToExpire = $daysToExpire;
    }



    public function getExpireDate(): ?DateTimeImmutable
    {
        return $this->daysToExpire !== null
            ? (new \DateTimeImmutable('now', new \DateTimeZone('Europe/Lisbon')))->modify('+' . $this->daysToExpire . ' days')->setTime(23, 59)
            : null;
    }


    /**
     * Converts the PayshopInitRequest object to an associative array.
     * @return array<string, string|null>
     */
    public function toPayload(): array
    {
        return [
            "payshopkey" => $this->payshopKey,
            "id"         => $this->orderId,
            "valor"      => $this->amount,
            "validade"   => $this->daysToExpire !== null ? DateTools::getFutureDate($this->daysToExpire)->format('Ymd') : null,
        ];
    }
}
