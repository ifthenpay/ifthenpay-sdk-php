<?php

namespace Ifthenpay\PaymentGateway\RequestObj;

use Ifthenpay\PaymentGateway\Model\CofidisCustomerData;
use Ifthenpay\PaymentGateway\Utils\Validation;

class CofidisInitRequest
{
    public string $cofidisKey;
    public string $orderId;
    public string $amount;
    public string $returnUrl;
    public ?string $description    = null;
    public string $customerName    = '';
    public string $customerVat     = '';
    public string $customerEmail   = '';
    public string $customerPhone   = '';
    public string $billingAddress  = '';
    public string $billingZipCode  = '';
    public string $billingCity     = '';
    public string $deliveryAddress = '';
    public string $deliveryZipCode = '';
    public string $deliveryCity    = '';




    public function __construct(
        string $cofidisKey,
        string $orderId,
        string $amount,
        string $returnUrl,
        ?string $description = null,
        ?CofidisCustomerData $customerData = null
    ) {
        $this->cofidisKey  = $cofidisKey;
        $this->orderId     = $orderId;
        $this->amount      = $amount;
        $this->returnUrl   = $returnUrl;
        $this->description = $description;

        if ($customerData) {
            $this->customerName    = $customerData->name ?? '';
            $this->customerVat     = $customerData->vat ?? '';
            $this->customerEmail   = $customerData->email ?? '';
            $this->customerPhone   = $customerData->phone ?? '';
            $this->billingAddress  = $customerData->billingAddress ?? '';
            $this->billingZipCode  = $customerData->billingZipCode ?? '';
            $this->billingCity     = $customerData->billingCity ?? '';
            $this->deliveryAddress = $customerData->deliveryAddress ?? '';
            $this->deliveryZipCode = $customerData->deliveryZipCode ?? '';
            $this->deliveryCity    = $customerData->deliveryCity ?? '';
        }




        Validation::validate(
            [
                'cofidisKey'      => $this->cofidisKey,
                'orderId'         => $this->orderId,
                'amount'          => $this->amount,
                'returnUrl'       => $this->returnUrl,
                'description'     => $this->description,
                'customerName'    => $this->customerName,
                'customerVat'     => $this->customerVat,
                'customerEmail'   => $this->customerEmail,
                'customerPhone'   => $this->customerPhone,
                'billingAddress'  => $this->billingAddress,
                'billingZipCode'  => $this->billingZipCode,
                'billingCity'     => $this->billingCity,
                'deliveryAddress' => $this->deliveryAddress,
                'deliveryZipCode' => $this->deliveryZipCode,
                'deliveryCity'    => $this->deliveryCity,
            ],
            [
                'cofidisKey'      => ['required', 'regex_key'],
                'orderId'         => ['required', 'max_len:25'],
                'amount'          => ['required', 'regex_money', 'max_len:10'],
                'returnUrl'       => ['required', 'url', 'max_len:200'],
                'description'     => ['nullable', 'max_len:100'],
                'customerName'    => ['nullable', 'max_len:100'],
                'customerVat'     => ['nullable', 'max_len:20'],
                'customerEmail'   => ['nullable', 'email', 'max_len:100'],
                'customerPhone'   => ['nullable', 'max_len:15'],
                'billingAddress'  => ['nullable', 'max_len:150'],
                'billingZipCode'  => ['nullable', 'max_len:20'],
                'billingCity'     => ['nullable', 'max_len:50'],
                'deliveryAddress' => ['nullable', 'max_len:150'],
                'deliveryZipCode' => ['nullable', 'max_len:20'],
                'deliveryCity'    => ['nullable', 'max_len:50'],
            ]
        );
    }


    /**
     * Converts the CofidisInitRequest object to an associative array.
     * @return array<string, string|null>
     */
    public function toPayload(): array
    {
        return [
            "orderId"         => $this->orderId,
            "amount"          => $this->amount,
            "returnUrl"       => $this->returnUrl,
            "description"     => $this->description,
            "customerName"    => $this->customerName,
            "customerVat"     => $this->customerVat,
            "customerEmail"   => $this->customerEmail,
            "customerPhone"   => $this->customerPhone,
            "billingAddress"  => $this->billingAddress,
            "billingZipCode"  => $this->billingZipCode,
            "billingCity"     => $this->billingCity,
            "deliveryAddress" => $this->deliveryAddress,
            "deliveryZipCode" => $this->deliveryZipCode,
            "deliveryCity"    => $this->deliveryCity
        ];
    }
}
