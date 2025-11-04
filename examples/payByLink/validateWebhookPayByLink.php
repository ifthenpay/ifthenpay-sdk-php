<?php

require_once '../../vendor/autoload.php';

use Ifthenpay\PaymentGateway\Enums\Status;
use Ifthenpay\PaymentGateway\IfthenpayGateway;
use Ifthenpay\PaymentGateway\Model\PayByLink;
use Ifthenpay\PaymentGateway\RequestObj\WebhookRequest;

echo '<h1>CREDIT CARD</h1>';
echo '<h3>testing validateWebhook</h3>';
try {

    $config = [
        'antiPhishingKey' => 'a0a0a0a0a0a0aa0a0a0a', // your anti phishing key here
        'backofficeKey'   => '1111-1111-1111-1111', // your backoffice key here
        'payByLink'       => [
            'key' => 'ITPG-000000', // your pay by link key here
        ]
    ];

    $ifthenpayGateway = new IfthenpayGateway($config);

    $storedPayment  = new PayByLink('0.50', '01', '1111111111', 'https://exampleurl.com', Status::PENDING, null);
    $webhookRequest = new WebhookRequest('0.50', '01', 'a0a0a0a0a0a0aa0a0a0a', 'tid111111111111');

    $ifthenpayGateway->payByLink()->validateWebhook($webhookRequest, $storedPayment);

    // You may also validate the transaction id using the isTransactionPaid method and the transaction id from the webhook request
    $result = $ifthenpayGateway->payByLink()->isTransactionPaid($webhookRequest->transactionId);

    // handle the result as needed

    echo '<p style="color:green;">SUCCESS</p>';
} catch (\Throwable $th) {
    echo '<p style="color:red;">ERROR</p>';
    echo '<pre>' . var_export($th, true) . '</pre>';
}
die();
