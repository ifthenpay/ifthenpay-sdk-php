<?php

require_once '../../vendor/autoload.php';

use Ifthenpay\PaymentGateway\Enums\Status;
use Ifthenpay\PaymentGateway\IfthenpayGateway;
use Ifthenpay\PaymentGateway\Model\CreditCard;

$config = [
    'creditCard' => [
        'key' => 'ITP-000000', // your credit card key here
    ],
];

$ifthenpayGateway = new IfthenpayGateway($config);

echo '<h1>CREDIT CARD</h1>';
echo '<h3>testing return</h3>';
try {

    $payment = new CreditCard('0.50', '01', 'tid111111111111', 'https://exampleurl.com', Status::PENDING, null);

    // simulating request with the "sk" received from the credit card gateway webhook
    $request = [
        'status'    => 'success',
        'id'        => '01',
        'amount'    => '0.50',
        'requestId' => 'tid111111111111',
        'sk'        => '49a1f83296a4ca06aa4d6062cfaee2b81c3ada4f1fffd447597042140c001561',
        'brand'     => 'VISA',
        'pan'       => '401200***1112'
    ];

    $ifthenpayGateway->creditCard()->verifyPayment($request['sk'], $payment);

    echo '<p style="color:green;">SUCCESS</p>';
} catch (\Throwable $th) {
    echo '<p style="color:red;">ERROR</p>';

    echo '<pre>' . var_export($th, true) . '</pre>';
}
die();
