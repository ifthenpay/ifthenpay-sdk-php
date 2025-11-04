<?php

namespace Ifthenpay\PaymentGateway\Model;

use DateTimeImmutable;
use Ifthenpay\PaymentGateway\Enums\Status;
use Ifthenpay\PaymentGateway\Interface\Model\PaymentInterface;
use Ifthenpay\PaymentGateway\Utils\DateTools;

class MultibancoOffline implements PaymentInterface
{
    private string $amount;
    private string $orderId;
    private string $entity;
    private string $reference;
    private Status $status;
    private ?DateTimeImmutable $expireDate;
    private ?DateTimeImmutable $createdAt;
    private ?DateTimeImmutable $updatedAt;

    public function __construct(string $amount, string $orderId, string $entity, string $reference, Status $status, ?DateTimeImmutable $expireDate = null, ?DateTimeImmutable $createdAt = null, ?DateTimeImmutable $updatedAt = null)
    {
        $this->amount     = $amount;
        $this->orderId    = $orderId;
        $this->entity     = $entity;
        $this->reference  = $reference;
        $this->expireDate = $expireDate;
        $this->status     = $status;
        $this->createdAt  = $createdAt;
        $this->updatedAt  = $updatedAt;
    }


    public function getOrderId(): string
    {
        return $this->orderId;
    }

    public function getAmount(): string
    {
        return $this->amount;
    }

    public function getEntity(): string
    {
        return $this->entity;
    }

    public function getReference(): string
    {
        return $this->reference;
    }

    public function getExpireDate(): ?DateTimeImmutable
    {
        return $this->expireDate;
    }

    public function getStatus(): Status
    {
        return $this->status;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getTransactionId(): ?string
    {
        return null;
    }

    /**
     * Converts the MultibancoOffline object to an associative array.
     * @return array<string, string|null>
     */
    public function toArray(): array
    {
        return [
            'amount'     => $this->amount,
            'orderId'    => $this->orderId,
            'entity'     => $this->entity,
            'reference'  => $this->reference,
            'status'     => $this->status->value,
            'expireDate' => $this->expireDate ? $this->expireDate->format(DateTools::DATE_FORMAT) : null,
            'createdAt'  => $this->createdAt ? $this->createdAt->format(DateTools::DATE_FORMAT) : null,
            'updatedAt'  => $this->updatedAt ? $this->updatedAt->format(DateTools::DATE_FORMAT) : null,
        ];
    }
}
