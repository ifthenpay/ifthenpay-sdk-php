<?php

require_once '../../vendor/autoload.php';

use Ifthenpay\PaymentGateway\Enums\Status;
use Ifthenpay\PaymentGateway\IfthenpayGateway;
use Ifthenpay\PaymentGateway\Model\Cofidis;

echo '<h1>COFIDIS</h1>';
echo '<h3>testing isExpired</h3>';

try {

    $config = [
        'cofidis' => [
            'key' => 'ITP-000000', // your cofidis key here
        ],
    ];

    $ifthenpayGateway = new IfthenpayGateway($config);

    // expired example
    $expiredDate = DateTimeImmutable::createFromMutable(new \DateTime('now - 10 minutes'));
    $payment     = new Cofidis('0.50', '01', 'tid111111111111', 'https://exampleurl.com', Status::PENDING, $expiredDate, null);
    $result      = $ifthenpayGateway->cofidis()->isExpired($payment);

    // not expired example
    // $expiredDate = DateTimeImmutable::createFromMutable(new \DateTime('now + 10 minutes'));
    // $payment = new cofidis('0.50', '01', 'tid111111111111', 'https://exampleurl.com', Status::PENDING, $expiredDate, null);
    // $result = $ifthenpayGateway->cofidis()->isExpired($payment);

    // without expiry date example
    // $payment = new cofidis('0.50', '01', 'tid111111111111', 'https://exampleurl.com', Status::PENDING);
    // $result = $ifthenpayGateway->cofidis()->isExpired($payment);


    echo '<p style="color:green;">SUCCESS</p>';
    echo '<p">The result is: ' . ($result ? 'true' : 'false') . '</p>';
} catch (\Throwable $th) {
    echo '<p style="color:red;">ERROR</p>';

    echo '<pre>' . var_export($th, true) . '</pre>';
}
die();
