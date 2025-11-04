<?php

use Ifthenpay\PaymentGateway\IfthenpayGateway;

class SdkFactory
{
    public static function make(): IfthenpayGateway
    {
        $config = require 'configClosure.php';

        $ifthenpayGateway = new IfthenpayGateway($config);
        return $ifthenpayGateway;
    }
}

// Usage
// $ifthenpayGateway = IfthenpaySdkFactory::make();

// Now you can use the $ifthenpayGateway instance to access payment methods
// For example, to create a MB WAY payment:
// $payment = $ifthenpayGateway->mbway()->initPayment('0103', '10,99', '919999999');
