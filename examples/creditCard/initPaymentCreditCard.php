<?php

require_once '../../vendor/autoload.php';

use Ifthenpay\PaymentGateway\IfthenpayGateway;

echo '<h1>CREDIT CARD</h1>';
echo '<h3>testing initPayment</h3>';

try {
    $config = [
        'language'   => 'pt', // optional, default is 'pt'
        'creditCard' => [
            'key'        => 'ITP-000000', // your credit card key here
            'successUrl' => 'https://exampleurl.com/success', // optional, if not provided will expect url in initPayment params
            'errorUrl'   => 'https://exampleurl.com/error', // optional, if not provided will expect url in initPayment params
            'cancelUrl'  => 'https://exampleurl.com/cancel', // optional, if not provided will expect url in initPayment params
            // 'minutesToExpire' => 30, // optional
        ]
    ];
    $ifthenpayGateway = new IfthenpayGateway($config);

    $creditCardRequestPayload = [
        'orderId' => '01',
        'amount'  => '0.50',
    ];

    $payment = $ifthenpayGateway->creditCard()->initPayment(...$creditCardRequestPayload);

    echo '<p style="color:green;">SUCCESS</p>';
    echo '<pre>' . var_export($payment->toArray(), true) . '</pre>';
    echo '<pre>' . var_export($payment, true) . '</pre>';
} catch (\Throwable $th) {
    echo '<p style="color:red;">ERROR</p>';
    echo '<pre>' . var_export($th, true) . '</pre>';
}

die();
