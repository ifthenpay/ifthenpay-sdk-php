<?php

use Ifthenpay\PaymentGateway\Service\ApiService;
use Ifthenpay\PaymentGateway\Config;
use Ifthenpay\PaymentGateway\Interface\Http\HttpClientInterface;
use Ifthenpay\PaymentGateway\RequestObj\RegisterWebhookRequest;
use Ifthenpay\PaymentGateway\RequestObj\MbwayInitRequest;
use Ifthenpay\PaymentGateway\RequestObj\MultibancoDynamicInitRequest;
use Ifthenpay\PaymentGateway\RequestObj\PayshopInitRequest;
use Ifthenpay\PaymentGateway\RequestObj\PixInitRequest;
use Ifthenpay\PaymentGateway\RequestObj\CreditCardInitRequest;
use Ifthenpay\PaymentGateway\RequestObj\CofidisInitRequest;
use Ifthenpay\PaymentGateway\RequestObj\IsPaidRequest;
use Mockery\Mock;
use Psr\Http\Message\ResponseInterface;



beforeEach(function () {

    $configArray = [
        'multibancoOffline' => [
            'entity'    => '12345',
            'subEntity' => '123',
        ],
        'backofficeKey'   => '1111-1111-1111-1111',
        'antiPhishingKey' => '1234123412341234',
    ];

    $this->config = Config::fromArray($configArray);
});

describe('[UNIT] ApiService', function () {

    it('registers webhook', function () {

        $request = Mockery::mock(RegisterWebhookRequest::class);
        $request->shouldReceive('toPayload')->andReturn(['foo' => 'bar']);

        $response = Mockery::mock(ResponseInterface::class);

        $httpClient = Mockery::mock(HttpClientInterface::class);
        $httpClient->shouldReceive('post')
            ->once()
            ->with($this->config->endpoint('register_webhook'), ['foo' => 'bar'])
            ->andReturn($response);

        $apiService = new ApiService($this->config, $httpClient);

        $result = $apiService->registerWebhook($request);
        expect($result)->toBe($response);
    });

    it('initiates Mbway payment', function () {
        $request = Mockery::mock(MbwayInitRequest::class);
        $request->shouldReceive('toPayload')->andReturn(['foo' => 'bar']);

        $response = Mockery::mock(ResponseInterface::class);

        $httpClient = Mockery::mock(HttpClientInterface::class);
        $httpClient->shouldReceive('post')
            ->once()
            ->with($this->config->endpoint('mbway_init'), ['foo' => 'bar'])
            ->andReturn($response);

        $apiService = new ApiService($this->config, $httpClient);

        $result = $apiService->initMbwayPayment($request);
        expect($result)->toBe($response);
    });

    it('initiates Multibanco Dynamic payment', function () {
        $request = Mockery::mock(MultibancoDynamicInitRequest::class);
        $request->shouldReceive('toPayload')->andReturn(['foo' => 'bar']);

        $response = Mockery::mock(ResponseInterface::class);

        $httpClient = Mockery::mock(HttpClientInterface::class);
        $httpClient->shouldReceive('post')
            ->once()
            ->with($this->config->endpoint('multibanco_init'), ['foo' => 'bar'])
            ->andReturn($response);

        $apiService = new ApiService($this->config, $httpClient);

        $result = $apiService->initMultibancoPayment($request);
        expect($result)->toBe($response);
    });

    it('initiates Payshop payment', function () {

        $request = Mockery::mock(PayshopInitRequest::class);
        $request->shouldReceive('toPayload')->andReturn(['foo' => 'bar']);

        $response = Mockery::mock(ResponseInterface::class);

        $httpClient = Mockery::mock(HttpClientInterface::class);
        $httpClient->shouldReceive('post')
            ->once()
            ->with($this->config->endpoint('payshop_init'), ['foo' => 'bar'])
            ->andReturn($response);

        $apiService = new ApiService($this->config, $httpClient);

        $result = $apiService->initPayshopPayment($request);
        expect($result)->toBe($response);
    });

    it('initiates Pix payment', function () {
        $request = Mockery::mock(PixInitRequest::class);
        $request->pixKey = 'some-pix-key';
        $request->shouldReceive('toPayload')->andReturn(['foo' => 'bar']);

        $response = Mockery::mock(ResponseInterface::class);

        $httpClient = Mockery::mock(HttpClientInterface::class);
        $httpClient->shouldReceive('post')
            ->once()
            ->with($this->config->endpoint('pix_init') . $request->pixKey, ['foo' => 'bar'])
            ->andReturn($response);

        $apiService = new ApiService($this->config, $httpClient);

        $result = $apiService->initPixPayment($request);
        expect($result)->toBe($response);
    });

    it('initiates Credit Card payment', function () {
        $request = Mockery::mock(CreditCardInitRequest::class);
        $request->creditCardKey = 'someCreditCardKey';
        $request->shouldReceive('toPayload')->andReturn(['foo' => 'bar']);

        $response = Mockery::mock(ResponseInterface::class);

        $httpClient = Mockery::mock(HttpClientInterface::class);
        $httpClient->shouldReceive('post')
            ->once()
            ->with($this->config->endpoint('creditcard_init') . $request->creditCardKey, ['foo' => 'bar'])
            ->andReturn($response);

        $apiService = new ApiService($this->config, $httpClient);

        $result = $apiService->initCreditCardPayment($request);
        expect($result)->toBe($response);
    });

    it('initiates Cofidis payment', function () {
        $request = Mockery::mock(CofidisInitRequest::class);
        $request->cofidisKey = 'someCofidisKey';
        $request->shouldReceive('toPayload')->andReturn(['foo' => 'bar']);

        $response = Mockery::mock(ResponseInterface::class);

        $httpClient = Mockery::mock(HttpClientInterface::class);
        $httpClient->shouldReceive('post')
            ->once()
            ->with($this->config->endpoint('cofidis_init') . $request->cofidisKey, ['foo' => 'bar'])
            ->andReturn($response);

        $apiService = new ApiService($this->config, $httpClient);

        $result = $apiService->initCofidisPayment($request);
        expect($result)->toBe($response);
    });

    it('checks if payment is paid', function () {
        $request = Mockery::mock(IsPaidRequest::class);
        $request->shouldReceive('toPayload')->andReturn(['foo' => 'bar']);

        $response = Mockery::mock(ResponseInterface::class);

        $httpClient = Mockery::mock(HttpClientInterface::class);
        $httpClient->shouldReceive('post')
            ->once()
            ->with($this->config->endpoint('list_payments'), ['foo' => 'bar'])
            ->andReturn($response);

        $apiService = new ApiService($this->config, $httpClient);

        $result = $apiService->isPaid($request);
        expect($result)->toBe($response);
    });


    it('gets Mbway payment status', function () {

        $mbwayKey = 'someMbwayKey';
        $transactionId = 'someTransactionId';
        $response = Mockery::mock(ResponseInterface::class);
        $httpClient = Mockery::mock(HttpClientInterface::class);
        $httpClient->shouldReceive('get')
            ->once()
            ->with($this->config->endpoint('mbway_status') . '?' . http_build_query(['mbWayKey' => $mbwayKey, 'requestId' => $transactionId]))
            ->andReturn($response);
        $apiService = new ApiService($this->config, $httpClient);
        $result = $apiService->getMbwayPaymentStatus($mbwayKey, $transactionId);
        expect($result)->toBe($response);
    });

    it('gets Cofidis payment status', function () {

        $cofidisKey = 'someCofidisKey';
        $transactionId = 'someTransactionId';
        $response = Mockery::mock(ResponseInterface::class);
        $httpClient = Mockery::mock(HttpClientInterface::class);
        $httpClient->shouldReceive('post')
            ->once()
            ->with($this->config->endpoint('cofidis_status'), ['cofidisKey' => $cofidisKey, 'requestId' => $transactionId])
            ->andReturn($response);
        $apiService = new ApiService($this->config, $httpClient);
        $result = $apiService->getCofidisPaymentStatus($cofidisKey, $transactionId);
        expect($result)->toBe($response);
    });
});
