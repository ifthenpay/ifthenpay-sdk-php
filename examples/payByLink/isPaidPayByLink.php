<?php

require_once '../../vendor/autoload.php';

use Ifthenpay\PaymentGateway\IfthenpayGateway;

echo '<h1>PAY BY LINK</h1>';
echo '<h3>testing isPaid</h3>';
try {

    $config = [
        'language'  => 'pt', // optional, default is 'pt'
        'payByLink' => [
            'key'            => 'ITPG-000000', // your pay by link key here
            'methodAccounts' => [
                '11111' => '111',
                'MBWAY' => 'ITP-000000',
            ],
            'successUrl'    => 'https://exampleurl.com/success', // optional, if not provided will expect url in initPayment params
            'errorUrl'      => 'https://exampleurl.com/error', // optional, if not provided will expect url in initPayment params
            'cancelUrl'     => 'https://exampleurl.com/cancel', // optional, if not provided will expect url in initPayment params
            'btnCloseUrl'   => 'https://exampleurl.com/close',
            'btnCloseLabel' => 'Close', // optional
            'defaultMethod' => 'MBWAY', // optional
            'daysToExpire'  => 3, // optional
        ]
    ];

    $ifthenpayGateway = new IfthenpayGateway($config);


    // 1 - request a paybylink
    $payByLinkPayment = $ifthenpayGateway->payByLink()->initPayment(
        orderId: '000001',
        amount: '10.00',
    );

    // when using instant payment methods (inside the PayByLink gateway) like credit card and pix you are redirected directly to the success url
    // and that url will have the transaction id as query string parameter 'tid'
    // In the case of other methods that transaction id is passed in the webhook, which it's advised to handle using the validateWebhook method

    // 2 - now, assuming a paid pay by link, you would be either redirected to the success url
    // that request would contain the transaction id

    $transactionId = 'tid111111111111';

    // 3 - you can now check if the transaction is paid
    $result = $ifthenpayGateway->payByLink()->isTransactionPaid($transactionId);

    echo '<p style="color:green;">SUCCESS</p>';
    echo '<p">The result is: ' . ($result ? $result->value : 'false') . '</p>';
} catch (\Throwable $th) {
    echo '<p style="color:red;">ERROR</p>';

    echo '<pre>' . var_export($th, true) . '</pre>';
}
die();
