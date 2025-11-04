<?php

namespace Ifthenpay\PaymentGateway\Interface\Model;

use DateTimeImmutable;
use Ifthenpay\PaymentGateway\Enums\Status;

interface PaymentInterface
{
    public function toArray(): array;
    public function getOrderId(): string;
    public function getAmount(): string;
    public function getTransactionId(): ?string;
    public function getReference(): ?string;
    public function getExpireDate(): ?DateTimeImmutable;
    public function getStatus(): Status;
    public function getCreatedAt(): ?DateTimeImmutable;
    public function getUpdatedAt(): ?DateTimeImmutable;
}
