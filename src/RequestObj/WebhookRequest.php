<?php

namespace Ifthenpay\PaymentGateway\RequestObj;

class WebhookRequest
{
    public string $amount;
    public string $orderId;
    public string $antiPhishingKey;
    public ?string $transactionId;
    public ?string $reference;


    public function __construct(string $amount, string $orderId, string $antiPhishingKey, ?string $transactionId = null, ?string $reference = null)
    {
        $this->amount          = $amount;
        $this->orderId         = $orderId;
        $this->transactionId   = $transactionId;
        $this->reference       = $reference;
        $this->antiPhishingKey = $antiPhishingKey;
    }

    /**
     * Converts the WebhookRequest object to an associative array.
     * @return array<string, string|null>
     */
    public function toArray(): array
    {
        return [
            'val' => $this->amount,
            'oid' => $this->orderId,
            'tid' => $this->transactionId,
            'ref' => $this->reference,
            'apk' => $this->antiPhishingKey,
        ];
    }
}
