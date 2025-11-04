<?php

require_once '../../vendor/autoload.php';

use Ifthenpay\PaymentGateway\Enums\Status;
use Ifthenpay\PaymentGateway\IfthenpayGateway;
use Ifthenpay\PaymentGateway\Model\PayByLink;

echo '<h1>Pay By Link</h1>';
echo '<h3>testing isExpired</h3>';

try {

    $config = [
        'payByLink' => [
            'key'            => 'ITPG-000000', // your pay by link key here
            'methodAccounts' => [
                '11111' => '111',
                'MBWAY' => 'ITP-000000',
            ],
        ]
    ];

    $ifthenpayGateway = new IfthenpayGateway($config);

    // expired example
    $expiredDate = DateTimeImmutable::createFromMutable(new \DateTime('now - 10 minutes'));
    $payment     = new PayByLink('0.50', '01', '1111111111', 'https://exampleurl.com', Status::PENDING, $expiredDate, null);
    $result      = $ifthenpayGateway->payByLink()->isExpired($payment);

    // not expired example
    // $expiredDate = DateTimeImmutable::createFromMutable(new \DateTime('now + 10 minutes'));
    // $payment = new PayByLink('0.50', '01', '1111111111', 'https://exampleurl.com', Status::PENDING, $expiredDate, null);
    // $result = $ifthenpayGateway->payByLink()->isExpired($payment);

    // without expiry date example
    // $payment = new PayByLink('0.50', '01', '1111111111', 'https://exampleurl.com', Status::PENDING);
    // $result = $ifthenpayGateway->payByLink()->isExpired($payment);


    echo '<p style="color:green;">SUCCESS</p>';
    echo '<p">The result is: ' . ($result ? 'true' : 'false') . '</p>';
} catch (\Throwable $th) {
    echo '<p style="color:red;">ERROR</p>';

    echo '<pre>' . var_export($th, true) . '</pre>';
}
die();
