<?php

require_once '../../vendor/autoload.php';

use Ifthenpay\PaymentGateway\Enums\Status;
use Ifthenpay\PaymentGateway\IfthenpayGateway;
use Ifthenpay\PaymentGateway\Model\Mbway;

echo '<h1>MBWAY</h1>';
echo '<h3>testing isExpired</h3>';

try {

    $config = [
        'mbway' => [
            'key' => 'ITP-000000', // your mbway key here
        ],
    ];

    $ifthenpayGateway = new IfthenpayGateway($config);

    // expired example
    $expiredDate = DateTimeImmutable::createFromMutable(new \DateTime('now - 10 minutes'));
    $payment     = new Mbway('0.50', '01', 'tid111111111111', '999999999', Status::PENDING, $expiredDate, null);
    $result      = $ifthenpayGateway->mbway()->isExpired($payment);

    // not expired example
    // $expiredDate = DateTimeImmutable::createFromMutable(new \DateTime('now + 10 minutes'));
    // $payment = new mbway('0.50', '01', 'tid111111111111', '999999999', Status::PENDING, $expiredDate, null);
    // $result = $ifthenpayGateway->mbway()->isExpired($payment);

    // without expiry date example
    // $payment = new mbway('0.50', '01', 'tid111111111111', '999999999', Status::PENDING);
    // $result = $ifthenpayGateway->mbway()->isExpired($payment);


    echo '<p style="color:green;">SUCCESS</p>';
    echo '<p">The result is: ' . ($result ? 'true' : 'false') . '</p>';
} catch (\Throwable $th) {
    echo '<p style="color:red;">ERROR</p>';

    echo '<pre>' . var_export($th, true) . '</pre>';
}
die();
