<?php

require_once '../../vendor/autoload.php';

use Ifthenpay\PaymentGateway\Enums\Status;
use Ifthenpay\PaymentGateway\IfthenpayGateway;
use Ifthenpay\PaymentGateway\Model\MultibancoOffline;

echo '<h1>Multibanco Offline</h1>';
echo '<h3>testing isPaid</h3>';

try {

    $config = [
        'backofficeKey'     => '1111-1111-1111-1111', // your backoffice key here
        'multibancoOffline' => [
            'entity'    => '11111', // your multibanco offline entity here
            'subEntity' => '111', // your multibanco offline subentity here
        ],
    ];

    $ifthenpayGateway = new IfthenpayGateway($config);

    $payment = new MultibancoOffline('0.50', '01', '11111', '111111111', Status::PENDING, null, null);

    $result = $ifthenpayGateway->multibancoOffline()->isPaid($payment);

    echo '<p style="color:green;">SUCCESS</p>';
    echo '<p">The result is: ' . ($result ? 'true' : 'false') . '</p>';
} catch (\Throwable $th) {
    echo '<p style="color:red;">ERROR</p>';

    echo '<pre>' . var_export($th, true) . '</pre>';
}
die();
