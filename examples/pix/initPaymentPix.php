<?php

require_once '../../vendor/autoload.php';

use Ifthenpay\PaymentGateway\IfthenpayGateway;

echo '<h1>Pix</h1>';
echo '<h3>testing initPayment</h3>';

try {

    $config = [
        'pix' => [
            'key' => 'ITP-000000', // your credit card key here
            // 'minutesToExpire' => 15, // optional
        ]
    ];

    $ifthenpayGateway = new IfthenpayGateway($config);

    $pixRequestPayload = [
        'orderId'      => '01',
        'amount'       => '0.50',
        'cpf'          => '111.111.111-11', // get this value from your customer
        'name'         => 'Example Name', // get this value from your customer
        'email'        => 'example@mail.com', // get this value from your customer
        'mobileNumber' => '999999999', // get this value from your customer
        'redirect'     => 'https://exampleurl.com',
        'description'  => 'example description', // optional
    ];

    $payment = $ifthenpayGateway->pix()->initPayment(...$pixRequestPayload);

    echo '<p style="color:green;">SUCCESS</p>';
    echo '<pre>' . var_export($payment->toArray(), true) . '</pre>';
    echo '<pre>' . var_export($payment, true) . '</pre>';
} catch (\Throwable $th) {
    echo '<p style="color:red;">ERROR</p>';
    echo '<pre>' . var_export($th, true) . '</pre>';
}

die();
