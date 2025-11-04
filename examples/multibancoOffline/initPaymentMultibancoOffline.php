<?php

require_once '../../vendor/autoload.php';

use Ifthenpay\PaymentGateway\IfthenpayGateway;

echo '<h1>Multibanco Offline</h1>';
echo '<h3>testing initPayment</h3>';

try {

    $config = [
        'multibancoOffline' => [
            'entity'    => '11111', // your multibanco offline entity here
            'subEntity' => '111', // your multibanco offline subentity here
            // 'daysToExpire' => 0, // optional
        ],
    ];

    $ifthenpayGateway = new IfthenpayGateway($config);

    $payload = [
        'orderId' => '01',
        'amount'  => '0.50',
    ];

    $multibancoofflinePayment = $ifthenpayGateway->multibancoOffline()->initPayment(...$payload);

    echo '<p style="color:green;">SUCCESS</p>';
    echo '<pre>' . var_export($multibancoofflinePayment->toArray(), true) . '</pre>';
    echo '<pre>' . var_export($multibancoofflinePayment, true) . '</pre>';
} catch (\Throwable $th) {
    echo '<p style="color:red;">ERROR</p>';
    echo '<pre>' . var_export($th, true) . '</pre>';
}

die();
