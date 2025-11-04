<?php

require_once '../../vendor/autoload.php';

use Ifthenpay\PaymentGateway\Enums\Status;
use Ifthenpay\PaymentGateway\IfthenpayGateway;
use Ifthenpay\PaymentGateway\Model\MultibancoDynamic;

echo '<h1>Multibanco Dynamic</h1>';
echo '<h3>testing isPaid</h3>';
try {

    $config = [
        'backofficeKey'     => '1111-1111-1111-1111', // your backoffice key here
        'multibancoDynamic' => [
            'key' => 'ITP-000000', // your multibanco dynamic key here
        ]
    ];

    $ifthenpayGateway = new IfthenpayGateway($config);

    $payment = new MultibancoDynamic('0.50', '01', '11111', '111111111', 'tid111111111111', Status::PENDING, null, null);

    $result = $ifthenpayGateway->multibancoDynamic()->isPaid($payment);

    echo '<p style="color:green;">SUCCESS</p>';
    echo '<p">The result is: ' . ($result ? 'true' : 'false') . '</p>';
} catch (\Throwable $th) {
    echo '<p style="color:red;">ERROR</p>';

    echo '<pre>' . var_export($th, true) . '</pre>';
}
die();
