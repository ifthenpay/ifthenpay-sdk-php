<?php

require_once '../../vendor/autoload.php';

use Ifthenpay\PaymentGateway\IfthenpayGateway;
use Ifthenpay\PaymentGateway\Model\CofidisCustomerData;

echo '<h1>COFIDIS</h1>';
echo '<h3>testing initPayment</h3>';

try {

    $config = [
        'backofficeKey' => '1111-1111-1111-1111', // your backoffice key here
        'cofidis'       => [
            'key'       => 'ITP-000000', // your cofidis key here
            'returnUrl' => 'https://exampleurl.com', // optional
            // 'minutesToExpire' => 60, // optional
        ]
    ];

    $ifthenpayGateway = new IfthenpayGateway($config);

    $customerData = new CofidisCustomerData(
        'Example Name', // requiered replace with customer name
        '222385758', // requiered replace with customer NIF
        'example@email.com', // requiered replace with customer email
        '+351999999999', // requiered replace with customer phone
        '',
        '',
        '',
        '',
        '',
        ''
    );

    $cofidisRequestPayload = [
        'orderId'      => '01',
        'amount'       => '100.00',
        'description'  => 'example description', // optional
        'customerData' => $customerData
    ];

    $payment = $ifthenpayGateway->cofidis()->initPayment(...$cofidisRequestPayload);

    echo '<p style="color:green;">SUCCESS</p>';
    echo '<pre>' . var_export($payment->toArray(), true) . '</pre>';
    echo '<pre>' . var_export($payment, true) . '</pre>';
} catch (\Throwable $th) {
    echo '<p style="color:red;">ERROR</p>';
    echo '<pre>' . var_export($th, true) . '</pre>';
}

die();
