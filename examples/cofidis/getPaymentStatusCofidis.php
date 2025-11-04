<?php

require_once '../../vendor/autoload.php';

use Ifthenpay\PaymentGateway\IfthenpayGateway;

echo '<h1>COFIDIS</h1>';
echo '<h3>testing getPaymentStatus</h3>';

try {

    $config = [
        'backofficeKey' => '1111-1111-1111-1111', // your backoffice key here
        'cofidis'       => [
            'key' => 'ITP-000000', // your cofidis key here
        ]
    ];

    $ifthenpayGateway = new IfthenpayGateway($config);

    $transactionId = 'tid111111111111'; // replace with a valid transaction id to test

    // you can specify the number of attempts as a second parameter, default is 3 attempts between which it waits increasing time intervals
    $cofidisPaymentStatus = $ifthenpayGateway->cofidis()->getPaymentStatus($transactionId, 1);

    echo '<p style="color:green;">SUCCESS</p>';
    echo '<pre>' . var_export($cofidisPaymentStatus, true) . '</pre>';
} catch (\Throwable $th) {
    echo '<p style="color:red;">ERROR</p>';

    echo '<pre>' . var_export($th, true) . '</pre>';
}
die();
