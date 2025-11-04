<?php

require_once '../../vendor/autoload.php';

use Ifthenpay\PaymentGateway\Enums\Status;
use Ifthenpay\PaymentGateway\IfthenpayGateway;
use Ifthenpay\PaymentGateway\Model\Cofidis;

echo '<h1>COFIDIS</h1>';
echo '<h3>testing isPaid</h3>';

try {
    $config = [
        'backofficeKey' => '1111-1111-1111-1111', // your backoffice key here
        'cofidis'       => [
            'key' => 'ITP-000000', // your cofidis key here
        ],
    ];

    $ifthenpayGateway = new IfthenpayGateway($config);


    $payment = new Cofidis('0.50', '01', 'tid111111111111', 'http://exampleurl.com', Status::PENDING, null);

    $result = $ifthenpayGateway->cofidis()->isPaid($payment);

    echo '<p style="color:green;">SUCCESS</p>';
    echo '<p">The result is: ' . ($result ? 'true' : 'false') . '</p>';
} catch (\Throwable $th) {
    echo '<p style="color:red;">ERROR</p>';

    echo '<pre>' . var_export($th, true) . '</pre>';
}
die();
