<?php

require_once '../../vendor/autoload.php';

use Ifthenpay\PaymentGateway\IfthenpayGateway;

echo '<h1>Multibanco Dynamic</h1>';
echo '<h3>testing initPayment</h3>';

try {
    $config = [
        'multibancoDynamic' => [
            'key' => 'ITP-000000', // your multibanco dynamic key here
            // 'daysToExpire' => 2, // optional
        ]
    ];

    $ifthenpayGateway = new IfthenpayGateway($config);

    $payload = [
        'orderId' => '01',
        'amount'  => '0.50',
        // 'description'  => 'multibancodynamic description', // optional
    ];

    $multibancodynamicPayment = $ifthenpayGateway->multibancodynamic()->initPayment(...$payload);

    echo '<p style="color:green;">SUCCESS</p>';
    echo '<pre>' . var_export($multibancodynamicPayment->toArray(), true) . '</pre>';
    echo '<pre>' . var_export($multibancodynamicPayment, true) . '</pre>';
} catch (\Throwable $th) {
    echo '<p style="color:red;">ERROR</p>';
    echo '<pre>' . var_export($th, true) . '</pre>';
}

die();
