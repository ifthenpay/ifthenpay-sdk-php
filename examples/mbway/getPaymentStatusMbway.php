<?php

use Ifthenpay\PaymentGateway\IfthenpayGateway;

require_once '../../vendor/autoload.php';

echo '<h1>MB WAY</h1>';
echo '<h3>testing getPaymentStatus</h3>';

try {

    $config = [
        'backofficeKey' => '1111-1111-1111-1111', // your backoffice key here
        'mbway'         => [
            'key' => 'ITP-000000', // your mbway key here
        ]
    ];

    $ifthenpayGateway = new IfthenpayGateway($config);

    $transactionId = 'tid111111111111'; // replace with a valid transaction id to test

    $mbwayPaymentStatus = $ifthenpayGateway->mbway()->getPaymentStatus($transactionId);

    echo '<p style="color:green;">SUCCESS</p>';
    echo '<pre>' . var_export($mbwayPaymentStatus, true) . '</pre>';
} catch (\Throwable $th) {
    echo '<p style="color:red;">ERROR</p>';

    echo '<pre>' . var_export($th, true) . '</pre>';
}
die();
