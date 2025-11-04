<?php

require_once '../../vendor/autoload.php';

use Ifthenpay\PaymentGateway\IfthenpayGateway;

echo '<h1>Payshop</h1>';
echo '<h3>testing initPayment</h3>';

try {

    $config = [
        'payshop' => [
            'key' => 'ITP-000000', // your mbway key here
            // 'daysToExpire' => 3, // optional
        ]
    ];

    $ifthenpayGateway = new IfthenpayGateway($config);

    $payload = [
        'orderId' => '01',
        'amount'  => '0.50',
    ];

    $payshopPayment = $ifthenpayGateway->payshop()->initPayment(...$payload);

    echo '<p style="color:green;">SUCCESS</p>';
    echo '<pre>' . var_export($payshopPayment->toArray(), true) . '</pre>';
    echo '<pre>' . var_export($payshopPayment, true) . '</pre>';
} catch (\Throwable $th) {
    echo '<p style="color:red;">ERROR</p>';
    echo '<pre>' . var_export($th, true) . '</pre>';
}

die();
