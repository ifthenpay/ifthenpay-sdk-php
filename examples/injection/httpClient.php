<?php

use Ifthenpay\PaymentGateway\IfthenpayGateway;

return function ($projectRoot) {
    require_once $projectRoot . '/vendor/autoload.php';

    echo '<h1>HTTP Client</h1>';
    echo '<h3>testing injection</h3>';
    try {

        $config = [
            'antiPhishingKey'   => '1234123412341234',
            'backofficeKey'     => '1111-1111-1111-1111',
            'multibancoDynamic' => [
                'key' => 'ITP-000000',
            ]
        ];

        // create instance of GuzzleHttp client to be injected, it can be any client that implements Psr\Http\Client\ClientInterface
        $client = new \GuzzleHttp\Client();

        $ifthenpayGateway = new IfthenpayGateway($config, $client);

        $payload = [
            'orderId'      => '01',
            'amount'       => '0.50',
            'description'  => 'multibancodynamic description',
            'daysToExpire' => 5
        ];

        $multibancodynamicPayment = $ifthenpayGateway->multibancodynamic()->initPayment(...$payload);

        echo '<p style="color:green;">SUCCESS</p>';
        echo '<pre>' . var_export($multibancodynamicPayment->toArray(), true) . '</pre>';
        echo '<pre>' . var_export($multibancodynamicPayment, true) . '</pre>';
    } catch (\Throwable $th) {
        echo '<p style="color:red;">ERROR</p>';

        echo '<pre>' . var_export($th, true) . '</pre>';
    }
    die();
};
