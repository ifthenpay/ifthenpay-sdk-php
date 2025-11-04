<?php

use Ifthenpay\PaymentGateway\IfthenpayGateway;

class IfthenpaySdkFactory
{
    public static function make(): IfthenpayGateway
    {
        $config = [
            'backofficeKey'   => '0000-0000-0000-0000',
            'antiPhishingKey' => 'abcdefgh123456788',
            'language'        => 'pt',
            'mbway'           => [
                'key'             => 'ITP-000000',
                'minutesToExpire' => 15,
            ],
            'multibancoDynamic' => [
                'key'          => 'ITP-000000',
                'daysToExpire' => 5,
            ],
            'multibancoOffline' => [
                'entity'       => '00000',
                'subEntity'    => '000',
                'daysToExpire' => 5,
            ]
        ];

        $ifthenpayGateway = new IfthenpayGateway($config);
        return $ifthenpayGateway;
    }
}

// Usage
// $ifthenpayGateway = IfthenpaySdkFactory::make();

// Now you can use the $ifthenpayGateway instance to access payment methods
// For example, to create a MB WAY payment:
// $payment = $ifthenpayGateway->mbway()->initPayment('0103', '10,99', '919999999');
