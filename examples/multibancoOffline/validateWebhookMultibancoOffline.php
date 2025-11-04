<?php

require_once '../../vendor/autoload.php';

use Ifthenpay\PaymentGateway\Enums\Status;
use Ifthenpay\PaymentGateway\IfthenpayGateway;
use Ifthenpay\PaymentGateway\Model\MultibancoOffline;
use Ifthenpay\PaymentGateway\RequestObj\WebhookRequest;

echo '<h1>Multibanco Offline</h1>';
echo '<h3>testing validateWebhook</h3>';

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

    $storedPayment  = new MultibancoOffline('0.50', '01', '11111', '111111111', Status::PENDING, null, null);
    $webhookRequest = new WebhookRequest('0.50', '01', 'a0a0a0a0a0a0aa0a0a0a', '', '111111111');

    $ifthenpayGateway->multibancoOffline()->validateWebhook($webhookRequest, $storedPayment);
    echo '<p style="color:green;">SUCCESS</p>';
} catch (\Throwable $th) {
    echo '<p style="color:red;">ERROR</p>';
    echo '<pre>' . var_export($th, true) . '</pre>';
}
die();
