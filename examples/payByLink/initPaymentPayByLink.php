<?php

require_once '../../vendor/autoload.php';

use Ifthenpay\PaymentGateway\IfthenpayGateway;

echo '<h1>PAY BY LINK</h1>';
echo '<h3>testing initPayment</h3>';

try {

    $config = [
        'language'  => 'pt', // optional, default is 'pt'
        'payByLink' => [
            'key'            => 'ITPG-000000', // your pay by link key here
            'methodAccounts' => [
                '11111' => '111',
                'MBWAY' => 'ITP-000000',
            ],
            'successUrl'       => 'https://exampleurl.com/success', // optional, if not provided will expect url in initPayment params
            'errorUrl'         => 'https://exampleurl.com/error', // optional, if not provided will expect url in initPayment params
            'cancelUrl'        => 'https://exampleurl.com/cancel', // optional, if not provided will expect url in initPayment params
            'btnCloseUrl'      => 'https://exampleurl.com/close', // optional, if not provided will expect url in initPayment params
            'btnCloseLabel'    => 'Close', // optional
            'defaultMethod'    => 'MULTIBANCO', // optional
            'daysToExpire'     => 3, // optional
            'isOneTimePayment' => true, // optional
        ]
    ];

    $ifthenpayGateway = new IfthenpayGateway($config);

    $payByLinkRequestPayload = [
        'orderId' => '01',
        'amount'  => '0.50',
    ];

    $payment = $ifthenpayGateway->payByLink()->initPayment(...$payByLinkRequestPayload);

    echo '<p style="color:green;">SUCCESS</p>';
    echo '<pre>' . var_export($payment->toArray(), true) . '</pre>';
    echo '<pre>' . var_export($payment, true) . '</pre>';
} catch (\Throwable $th) {
    echo '<p style="color:red;">ERROR</p>';
    echo '<pre>' . var_export($th, true) . '</pre>';
}

die();
