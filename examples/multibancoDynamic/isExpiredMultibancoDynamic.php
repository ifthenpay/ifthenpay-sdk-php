<?php

require_once '../../vendor/autoload.php';

use Ifthenpay\PaymentGateway\Enums\Status;
use Ifthenpay\PaymentGateway\IfthenpayGateway;
use Ifthenpay\PaymentGateway\Model\MultibancoDynamic;

echo '<h1>Multibanco Dynamic</h1>';
echo '<h3>testing isExpired</h3>';

try {

    $config = [
        'multibancoDynamic' => [
            'key' => 'ITP-000000', // your multibanco dynamic key here
        ],
    ];

    $ifthenpayGateway = new IfthenpayGateway($config);

    // expired example
    $expiredDate = DateTimeImmutable::createFromMutable(new \DateTime('now - 10 minutes'));
    $payment     = new MultibancoDynamic('0.50', '01', '11111', '111111111', 'tid111111111111', Status::PENDING, $expiredDate, null);
    $result      = $ifthenpayGateway->multibancoDynamic()->isExpired($payment);

    // not expired example
    // $expiredDate = DateTimeImmutable::createFromMutable(new \DateTime('now + 10 minutes'));
    // $payment = new multibancoDynamic('0.50', '01', '11111', '111111111', 'tid111111111111', Status::PENDING, $expiredDate, null);
    // $result = $ifthenpayGateway->multibancoDynamic()->isExpired($payment);

    // without expiry date example
    // $payment = new multibancoDynamic('0.50', '01', '11111', '111111111', 'tid111111111111', Status::PENDING);
    // $result = $ifthenpayGateway->multibancoDynamic()->isExpired($payment);


    echo '<p style="color:green;">SUCCESS</p>';
    echo '<p">The result is: ' . ($result ? 'true' : 'false') . '</p>';
} catch (\Throwable $th) {
    echo '<p style="color:red;">ERROR</p>';
    echo '<pre>' . var_export($th, true) . '</pre>';
}
die();
