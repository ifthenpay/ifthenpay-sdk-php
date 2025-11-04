<?php

require_once '../../vendor/autoload.php';

use Ifthenpay\PaymentGateway\Enums\Status;
use Ifthenpay\PaymentGateway\IfthenpayGateway;
use Ifthenpay\PaymentGateway\Model\MultibancoOffline;

echo '<h1>Multibanco Offline</h1>';
echo '<h3>testing isExpired</h3>';

try {

    $config = [
        'multibancoOffline' => [
            'entity'    => '11111', // your multibanco offline entity here
            'subEntity' => '111', // your multibanco offline subentity here
        ],
    ];

    $ifthenpayGateway = new IfthenpayGateway($config);

    // expired example
    $expiredDate = DateTimeImmutable::createFromMutable(new \DateTime('now - 10 minutes'));
    $payment     = new MultibancoOffline('0.50', '01', '11111', '111111111', Status::PENDING, $expiredDate, null);
    $result      = $ifthenpayGateway->multibancoOffline()->isExpired($payment);

    // not expired example
    // $expiredDate = DateTimeImmutable::createFromMutable(new \DateTime('now + 10 minutes'));
    // $payment     = new MultibancoOffline('0.50', '01', '11111', '111111111', Status::PENDING, $expiredDate, null);
    // $result = $ifthenpayGateway->multibancoOffline()->isExpired($payment);

    // without expiry date example
    // $payment     = new MultibancoOffline('0.50', '01', '11111', '111111111', Status::PENDING, null, null);
    // $result = $ifthenpayGateway->multibancoOffline()->isExpired($payment);


    echo '<p style="color:green;">SUCCESS</p>';
    echo '<p">The result is: ' . ($result ? 'true' : 'false') . '</p>';
} catch (\Throwable $th) {
    echo '<p style="color:red;">ERROR</p>';
    echo '<pre>' . var_export($th, true) . '</pre>';
}
die();
