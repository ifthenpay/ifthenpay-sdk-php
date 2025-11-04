<?php

require_once '../../vendor/autoload.php';

use Ifthenpay\PaymentGateway\IfthenpayGateway;

echo '<h1>PAY BY LINK</h1>';
echo '<h3>testing registerWebhook</h3>';

try {

    // you will need to have set the methodAccounts in the config for this to work
    // that is because the webhook is registered per method account
    $config = [
        'antiPhishingKey' => 'a0a0a0a0a0a0aa0a0a0a', // your anti phishing key here
        'backofficeKey'   => '1111-1111-1111-1111', // your backoffice key here
        'payByLink'       => [
            'key'            => 'ITPG-000000', // your pay by link key here
            'methodAccounts' => [
                'MBWAY' => 'ITP-000000',
            ],
        ],
    ];

    $ifthenpayGateway = new IfthenpayGateway($config);

    $registeredUrl = $ifthenpayGateway->payByLink()->registerWebhook('https://testurl2.com');

    echo '<p style="color:green;">SUCCESS</p>';
    echo '<p">Registered the webhook url:</p>';
    echo '<p style="color:blue;">' . $registeredUrl . '</p>';
} catch (\Throwable $th) {
    echo '<p style="color:red;">ERROR</p>';

    echo '<pre>' . var_export($th, true) . '</pre>';
}
die();
