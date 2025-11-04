<?php

require_once '../../vendor/autoload.php';

use Ifthenpay\PaymentGateway\Enums\Status;
use Ifthenpay\PaymentGateway\IfthenpayGateway;
use Ifthenpay\PaymentGateway\Model\Payshop;
use Ifthenpay\PaymentGateway\RequestObj\WebhookRequest;

echo '<h1>Payshop</h1>';
echo '<h3>testing validateWebhook</h3>';

try {
    $config = [
        'antiPhishingKey' => 'a0a0a0a0a0a0aa0a0a0a', // your anti phishing key here
        'backofficeKey'   => '1111-1111-1111-1111', // your backoffice key here
        'payshop'         => [
            'key' => 'ITP-000000', // your payshop key here
        ],
    ];

    $ifthenpayGateway = new IfthenpayGateway($config);

    $storedPayment  = new Payshop('0.50', '01', 'tid111111111111', '1111111111111', Status::PENDING, null);
    $webhookRequest = new WebhookRequest('0.50', '01', 'a0a0a0a0a0a0aa0a0a0a', 'tid111111111111', '1111111111111');

    $ifthenpayGateway->payshop()->validateWebhook($webhookRequest, $storedPayment);
    echo '<p style="color:green;">SUCCESS</p>';
} catch (\Throwable $th) {
    echo '<p style="color:red;">ERROR</p>';
    echo '<pre>' . var_export($th, true) . '</pre>';
}
die();
