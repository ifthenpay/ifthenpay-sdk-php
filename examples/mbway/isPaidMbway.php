<?php

require_once '../../vendor/autoload.php';

use Ifthenpay\PaymentGateway\Enums\Status;
use Ifthenpay\PaymentGateway\IfthenpayGateway;
use Ifthenpay\PaymentGateway\Model\Mbway;

echo '<h1>MB WAY</h1>';
echo '<h3>testing isPaid</h3>';

$config = [
    'backofficeKey' => '1111-1111-1111-1111', // your backoffice key here
    'mbway'         => [
        'key' => 'ITP-000000', // your mbway key here
    ],
];

$ifthenpayGateway = new IfthenpayGateway($config);

try {

    $payment = new Mbway('0.50', '01', 'tid111111111111', '351#999999999', Status::PENDING, null, null);

    $result = $ifthenpayGateway->mbway()->isPaid($payment);

    echo '<p style="color:green;">SUCCESS</p>';
    echo '<p">The result is: ' . ($result ? 'true' : 'false') . '</p>';
} catch (\Throwable $th) {
    echo '<p style="color:red;">ERROR</p>';

    echo '<pre>' . var_export($th, true) . '</pre>';
}
die();
