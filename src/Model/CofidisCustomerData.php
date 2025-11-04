<?php

namespace Ifthenpay\PaymentGateway\Model;

class CofidisCustomerData
{
    public string $name;
    public string $vat;
    public string $email;
    public string $phone;
    public string $billingAddress;
    public string $billingZipCode;
    public string $billingCity;
    public string $deliveryAddress;
    public string $deliveryZipCode;
    public string $deliveryCity;



    public function __construct(string $name, string $vat, string $email, string $phone, string $billingAddress = '', string $billingZipCode = '', string $billingCity = '', string $deliveryAddress = '', string $deliveryZipCode = '', string $deliveryCity = '')
    {
        $this->name            = $name;
        $this->vat             = $vat;
        $this->email           = $email;
        $this->phone           = $phone;
        $this->billingAddress  = $billingAddress;
        $this->billingZipCode  = $billingZipCode;
        $this->billingCity     = $billingCity;
        $this->deliveryAddress = $deliveryAddress;
        $this->deliveryZipCode = $deliveryZipCode;
        $this->deliveryCity    = $deliveryCity;
    }



    /**
     * Converts the CofidisCustomerData object to an associative array.
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'customerName'    => $this->name,
            'customerVat'     => $this->vat,
            'customerEmail'   => $this->email,
            'customerPhone'   => $this->phone,
            'billingAddress'  => $this->billingAddress,
            'billingZipCode'  => $this->billingZipCode,
            'billingCity'     => $this->billingCity,
            'deliveryAddress' => $this->deliveryAddress,
            'deliveryZipCode' => $this->deliveryZipCode,
            'deliveryCity'    => $this->deliveryCity,
        ];
    }
}
