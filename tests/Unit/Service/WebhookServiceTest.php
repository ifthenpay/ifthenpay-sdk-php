<?php

use Ifthenpay\PaymentGateway\Config;
use Ifthenpay\PaymentGateway\Exception\EndpointResponseException;
use Ifthenpay\PaymentGateway\Exception\WebhookValidationException;
use Ifthenpay\PaymentGateway\RequestObj\RegisterWebhookRequest;
use Ifthenpay\PaymentGateway\Service\ApiService;
use Ifthenpay\PaymentGateway\Service\WebhookService;
use Psr\Http\Message\ResponseInterface;

beforeEach(function () {

    $this->config = Config::fromArray([
        'backofficeKey'   => '1111-1111-1111-1111',
        'antiPhishingKey' => '1234123412341234',
    ]);
});

describe('[UNIT] WebhookService', function () {

    it('registers webhook successfully', function () {

        $response = Mockery::mock(ResponseInterface::class);
        $response->shouldReceive('getStatusCode')->andReturn(200);
        $response->shouldReceive('getBody->getContents')->andReturn('OK: Webhook registered');

        $mockApiService = Mockery::mock(ApiService::class);
        $mockApiService->shouldReceive('registerWebhook')
            ->once()
            ->with(Mockery::type(RegisterWebhookRequest::class))
            ->andReturn($response);

        $webhookService = new WebhookService($this->config, $mockApiService);

        expect($webhookService->registerWebhook('MBWAY', 'AAA-123456', 'https://example.com/webhook'))->not->toThrow(Ifthenpay\PaymentGateway\Exception\EndpointResponseException::class);
    });

    it('throws exception on non-200 response', function () {

        $response = Mockery::mock(ResponseInterface::class);
        $response->shouldReceive('getStatusCode')->andReturn(500);
        $response->shouldReceive('getBody->getContents')->andReturn('Internal Server Error');

        $mockApiService = Mockery::mock(ApiService::class);
        $mockApiService->shouldReceive('registerWebhook')
            ->once()
            ->with(Mockery::type(RegisterWebhookRequest::class))
            ->andReturn($response);

        $webhookService = new WebhookService($this->config, $mockApiService);

        $webhookService->registerWebhook('MBWAY', 'AAA-123456', 'https://example.com/webhook');
    })->throws(EndpointResponseException::class, 'Error unexpected response code');

    it('throws exception on failure response text', function () {

        $response = Mockery::mock(ResponseInterface::class);
        $response->shouldReceive('getStatusCode')->andReturn(200);
        $response->shouldReceive('getBody->getContents')->andReturn('ERROR: Invalid key');

        $mockApiService = Mockery::mock(ApiService::class);
        $mockApiService->shouldReceive('registerWebhook')
            ->once()
            ->with(Mockery::type(RegisterWebhookRequest::class))
            ->andReturn($response);

        $webhookService = new WebhookService($this->config, $mockApiService);

        $webhookService->registerWebhook('MBWAY', 'AAA-123456', 'https://example.com/webhook');
    })->throws(EndpointResponseException::class, 'Failure to register callback');


    it('validates webhook successfully', function () {

        $mockApiService = Mockery::mock(ApiService::class);
        $webhookService = new WebhookService($this->config, $mockApiService);
        $webhookRequest = [
            'oid' => '123456',
            'val' => '100.00',
            'apk' => $this->config->antiPhishingKey(),
        ];

        $payment = [
            'orderId' => '123456',
            'amount' => '100.00',
            'antiPhishingKey' => $this->config->antiPhishingKey(),
        ];

        expect($webhookService->validateWebhook($webhookRequest, $payment))->not->toThrow(WebhookValidationException::class);
    });

    it('throws exception if antiPhishingKey does not match', function () {

        $mockApiService = Mockery::mock(ApiService::class);
        $webhookService = new WebhookService($this->config, $mockApiService);
        $webhookRequest = [
            'oid' => '123456',
            'val' => '100.00',
            'apk' => 'wrongkey',
        ];

        $payment = [
            'orderId' => '123456',
            'amount' => '100.00',
            'antiPhishingKey' => $this->config->antiPhishingKey(),
        ];

        $webhookService->validateWebhook($webhookRequest, $payment);
    })->throws(WebhookValidationException::class, 'AntiPhishingKey does not match');

    it('throws exception if amount does not match', function () {

        $mockApiService = Mockery::mock(ApiService::class);
        $webhookService = new WebhookService($this->config, $mockApiService);
        $webhookRequest = [
            'oid' => '123456',
            'val' => '200.00',
            'apk' => $this->config->antiPhishingKey(),
        ];

        $payment = [
            'orderId' => '123456',
            'amount' => '100.00',
            'antiPhishingKey' => $this->config->antiPhishingKey(),
        ];

        $webhookService->validateWebhook($webhookRequest, $payment);
    })->throws(WebhookValidationException::class, 'Amount does not match');

    it('throws exception if orderId does not match', function () {

        $mockApiService = Mockery::mock(ApiService::class);
        $webhookService = new WebhookService($this->config, $mockApiService);
        $webhookRequest = [
            'oid' => '654321',
            'val' => '100.00',
            'apk' => $this->config->antiPhishingKey(),
        ];

        $payment = [
            'orderId' => '123456',
            'amount' => '100.00',
            'antiPhishingKey' => $this->config->antiPhishingKey(),
        ];

        $webhookService->validateWebhook($webhookRequest, $payment);
    })->throws(WebhookValidationException::class, 'OrderId does not match');

    it('throws exception if missing webhook parameter (oid)', function () {

        $mockApiService = Mockery::mock(ApiService::class);
        $webhookService = new WebhookService($this->config, $mockApiService);
        $webhookRequest = [
            'val' => '100.00',
            'apk' => $this->config->antiPhishingKey(),
        ];

        $payment = [
            'orderId' => '123456',
            'amount' => '100.00',
            'antiPhishingKey' => $this->config->antiPhishingKey(),
        ];

        $webhookService->validateWebhook($webhookRequest, $payment);
    })->throws(WebhookValidationException::class, 'Missing webhook parameter');

    it('throws exception if missing webhook parameter (val)', function () {

        $mockApiService = Mockery::mock(ApiService::class);
        $webhookService = new WebhookService($this->config, $mockApiService);
        $webhookRequest = [
            'oid' => '123456',
            'apk' => $this->config->antiPhishingKey(),
        ];

        $payment = [
            'orderId' => '123456',
            'amount' => '100.00',
            'antiPhishingKey' => $this->config->antiPhishingKey(),
        ];

        $webhookService->validateWebhook($webhookRequest, $payment);
    })->throws(WebhookValidationException::class, 'Missing webhook parameter');

    it('throws exception if missing webhook parameter (apk)', function () {

        $mockApiService = Mockery::mock(ApiService::class);
        $webhookService = new WebhookService($this->config, $mockApiService);
        $webhookRequest = [
            'oid' => '123456',
            'val' => '100.00',
        ];

        $payment = [
            'orderId' => '123456',
            'amount' => '100.00',
            'antiPhishingKey' => $this->config->antiPhishingKey(),
        ];

        $webhookService->validateWebhook($webhookRequest, $payment);
    })->throws(WebhookValidationException::class, 'Missing webhook parameter');


    it('throws exception if extra param missing', function () {

        $mockApiService = Mockery::mock(ApiService::class);
        $webhookService = new WebhookService($this->config, $mockApiService);
        $webhookRequest = [
            'oid' => '123456',
            'val' => '100.00',
            'apk' => $this->config->antiPhishingKey(),
        ];

        $payment = [
            'orderId' => '123456',
            'amount' => '100.00',
            'antiPhishingKey' => $this->config->antiPhishingKey(),
            'additionalParam' => 'someValue',
        ];

        $webhookService->validateWebhook($webhookRequest, $payment, ['adp' => 'additionalParam']);
    })->throws(WebhookValidationException::class, 'Missing webhook parameter adp');

    it('validates webhook with extra param successfully', function () {

        $mockApiService = Mockery::mock(ApiService::class);
        $webhookService = new WebhookService($this->config, $mockApiService);
        $webhookRequest = [
            'oid' => '123456',
            'val' => '100.00',
            'apk' => $this->config->antiPhishingKey(),
            'adp' => 'someValue',
        ];

        $payment = [
            'orderId' => '123456',
            'amount' => '100.00',
            'antiPhishingKey' => $this->config->antiPhishingKey(),
            'additionalParam' => 'someValue',
        ];

        expect($webhookService->validateWebhook($webhookRequest, $payment, ['adp' => 'additionalParam']))->not->toThrow(WebhookValidationException::class);
    });

    it('throws exception if extra param does not match', function () {

        $mockApiService = Mockery::mock(ApiService::class);
        $webhookService = new WebhookService($this->config, $mockApiService);
        $webhookRequest = [
            'oid' => '123456',
            'val' => '100.00',
            'apk' => $this->config->antiPhishingKey(),
            'adp' => 'wrongValue',
        ];

        $payment = [
            'orderId' => '123456',
            'amount' => '100.00',
            'antiPhishingKey' => $this->config->antiPhishingKey(),
            'additionalParam' => 'someValue',
        ];

        $webhookService->validateWebhook($webhookRequest, $payment, ['adp' => 'additionalParam']);
    })->throws(WebhookValidationException::class, 'AdditionalParam does not match');
});
