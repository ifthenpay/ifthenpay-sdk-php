<?php

require_once '../../vendor/autoload.php';

use Ifthenpay\PaymentGateway\Enums\Status;
use Ifthenpay\PaymentGateway\IfthenpayGateway;
use Ifthenpay\PaymentGateway\Model\CreditCard;

echo '<h1>CREDIT CARD</h1>';
echo '<h3>testing isExpired</h3>';

try {

    $config = [
        'creditCard' => [
            'key' => 'ITP-000000', // your credit card key here
        ],
    ];

    $ifthenpayGateway = new IfthenpayGateway($config);

    // expired example
    $expiredDate = DateTimeImmutable::createFromMutable(new \DateTime('now - 10 minutes'));
    $payment     = new CreditCard('0.50', '01', 'tid111111111111', 'https://exampleurl.com', Status::PENDING, $expiredDate, null);
    $result      = $ifthenpayGateway->creditCard()->isExpired($payment);

    // not expired example
    // $expiredDate = DateTimeImmutable::createFromMutable(new \DateTime('now + 10 minutes'));
    // $payment = new CreditCard('0.50', '01', 'tid111111111111', 'https://exampleurl.com', Status::PENDING, $expiredDate, null);
    // $result = $ifthenpayGateway->creditCard()->isExpired($payment);

    // without expiry date example
    // $payment = new CreditCard('0.50', '01', 'tid111111111111', 'https://exampleurl.com', Status::PENDING);
    // $result = $ifthenpayGateway->creditCard()->isExpired($payment);


    echo '<p style="color:green;">SUCCESS</p>';
    echo '<p">The result is: ' . ($result ? 'true' : 'false') . '</p>';
} catch (\Throwable $th) {
    echo '<p style="color:red;">ERROR</p>';

    echo '<pre>' . var_export($th, true) . '</pre>';
}
die();
