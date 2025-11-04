<?php

require_once '../../vendor/autoload.php';

use Ifthenpay\PaymentGateway\IfthenpayGateway;

echo '<h1>MB WAY</h1>';
echo '<h3>testing initPayment</h3>';

try {

    $config = [
        'mbway' => [
            'key' => 'ITP-000000', // your mbway key here
            // 'minutesToExpire' => 15, // optional
        ]
    ];
    $ifthenpayGateway = new IfthenpayGateway($config);

    $mbwayRequestPayload = [
        'orderId'      => '01',
        'amount'       => '0.50',
        'mobileNumber' => '351#999999999', // get this value from your customer
        'description'  => 'mbway description', // optional
        'email'        => 'example@mail.com' // optional
    ];

    $payment = $ifthenpayGateway->mbway()->initPayment(...$mbwayRequestPayload);

    echo '<p style="color:green;">SUCCESS</p>';
    echo '<pre>' . var_export($payment->toArray(), true) . '</pre>';
    echo '<pre>' . var_export($payment, true) . '</pre>';
} catch (\Throwable $th) {
    echo '<p style="color:red;">ERROR</p>';
    echo '<pre>' . var_export($th, true) . '</pre>';
}

die();
