<?php

require_once '../../vendor/autoload.php';

use Ifthenpay\PaymentGateway\IfthenpayGateway;

echo '<h1>Multibanco Offline</h1>';
echo '<h3>testing registerWebhook</h3>';

try {

    $config = [
        'antiPhishingKey'   => 'a0a0a0a0a0a0aa0a0a0a', // your anti phishing key here
        'backofficeKey'     => '1111-1111-1111-1111', // your backoffice key here
        'multibancoOffline' => [
            'entity'    => '11111', // your multibanco offline entity here
            'subEntity' => '111', // your multibanco offline subentity here
        ],
    ];

    $ifthenpayGateway = new IfthenpayGateway($config);

    $registeredUrl = $ifthenpayGateway->multibancoOffline()->registerWebhook('https://exampleurl.com');

    echo '<p style="color:green;">SUCCESS</p>';
    echo '<p">Registered the webhook url:</p>';
    echo '<p style="color:blue;">' . $registeredUrl . '</p>';
} catch (\Throwable $th) {
    echo '<p style="color:red;">ERROR</p>';
    echo '<pre>' . var_export($th, true) . '</pre>';
}
die();
