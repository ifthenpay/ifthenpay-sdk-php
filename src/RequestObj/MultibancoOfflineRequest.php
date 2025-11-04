<?php

declare(strict_types=1);

namespace Ifthenpay\PaymentGateway\RequestObj;

use Ifthenpay\PaymentGateway\Utils\Validation;

class MultibancoOfflineRequest
{
    public string $entity;
    public string $subEntity;
    public string $orderId;
    public string $amount;


    public function __construct(
        string $entity,
        string $subEntity,
        string $orderId,
        string $amount
    ) {
        $this->entity    = $entity;
        $this->subEntity = $subEntity;
        $this->orderId   = $orderId;
        $this->amount    = $amount;

        Validation::validate(
            [
                'entity'    => $this->entity,
                'subEntity' => $this->subEntity,
                'orderId'   => $this->orderId,
                'amount'    => $this->amount,
            ],
            [
                'entity'    => ['regex_mb_entity'],
                'subEntity' => ['regex_mb_subentity'],
                'orderId'   => ['required', 'max_len:25'],
                'amount'    => ['required', 'regex_money', 'max_len:10'],
            ]
        );
    }


    /**
     * Converts the MultibancoOfflineRequest object to an associative array.
     * @return array<string, string>
     */
    public function toPayload(): array
    {
        return [
            "entity"    => $this->entity,
            "subEntity" => $this->subEntity,
            "orderId"   => $this->orderId,
            "amount"    => $this->amount,
        ];
    }
}
