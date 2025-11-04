<?php

namespace Ifthenpay\PaymentGateway\Model;

use DateTimeImmutable;
use Ifthenpay\PaymentGateway\Enums\Status;
use Ifthenpay\PaymentGateway\Interface\Model\PaymentInterface;
use Ifthenpay\PaymentGateway\Utils\DateTools;

class PayByLink implements PaymentInterface
{
    public string $amount;
    public string $orderId;
    public string $pinCode;
    public string $paymentUrl;
    public Status $status;
    public ?DateTimeImmutable $expireDate;
    public ?DateTimeImmutable $createdAt;
    public ?DateTimeImmutable $updatedAt;

    public function __construct(string $amount, string $orderId, string $pinCode, string $paymentUrl, Status $status, ?DateTimeImmutable $expireDate = null, ?DateTimeImmutable $createdAt = null, ?DateTimeImmutable $updatedAt = null)
    {
        $this->orderId    = $orderId;
        $this->pinCode    = $pinCode;
        $this->amount     = $amount;
        $this->paymentUrl = $paymentUrl;
        $this->status     = $status;
        $this->expireDate = $expireDate;
        $this->createdAt  = $createdAt;
        $this->updatedAt  = $updatedAt;
    }

    public function getOrderId(): string
    {
        return $this->orderId;
    }

    public function getPinCode(): string
    {
        return $this->pinCode;
    }

    public function getAmount(): string
    {
        return $this->amount;
    }

    public function getPaymentUrl(): string
    {
        return $this->paymentUrl;
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

    public function getReference(): ?string
    {
        return null;
    }

    public function getTransactionId(): ?string
    {
        return null;
    }

    public function getExpireDate(): ?DateTimeImmutable
    {
        return $this->expireDate;
    }



    /**
     * Converts the PayByLink object to an associative array.
     * @return array<string, string|null>
     */
    public function toArray(): array
    {
        return [
            'orderId'    => $this->orderId,
            'pinCode'    => $this->pinCode,
            'amount'     => $this->amount,
            'paymentUrl' => $this->paymentUrl,
            'status'     => $this->status->value,
            'expireDate' => $this->expireDate ? $this->expireDate->format(DateTools::DATE_FORMAT) : null,
            'createdAt'  => $this->createdAt ? $this->createdAt->format(DateTools::DATE_FORMAT) : null,
            'updatedAt'  => $this->updatedAt ? $this->updatedAt->format(DateTools::DATE_FORMAT) : null,
        ];
    }
}
