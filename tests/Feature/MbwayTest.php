<?php

use Ifthenpay\PaymentGateway\Config;
use Ifthenpay\PaymentGateway\Enums\Status;
use Ifthenpay\PaymentGateway\IfthenpayGateway;
use Ifthenpay\PaymentGateway\Model\Mbway;
use Ifthenpay\PaymentGateway\Service\MbwayService;
use Ifthenpay\PaymentGateway\Utils\DateTools;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

beforeEach(function () {

    $this->configArray = [
        'mbway' => [
            'key'    => 'ITP-000000',
        ],
        'backofficeKey'   => '1111-1111-1111-1111',
        'antiPhishingKey' => '1234123412341234',
    ];

    $this->config = Config::fromArray($this->configArray);
});


describe('[FEATURE] Mbway initPayment', function () {
    it('initializes payment successfully', function () {

        $requestPayload = [
            'mbWayKey'        => 'ITP-000000',
            'orderId'    => '01',
            'amount'     => '0.50',
            'mobileNumber'      => '912345678',
            'email'  => null,
            'description' => 'description'
        ];

        $responsePayload = [
            'Amount'    => '0.50',
            'OrderId'   => '01',
            'Message'   => 'Success',
            'Status'    => MbwayService::INIT_STATUS_SUCCESS,
            'RequestId' => 'CmOc7XIjeLxy5Rlf1uQo',
        ];

        $mockStream = Mockery::mock(StreamInterface::class);
        $mockStream->shouldReceive('__toString')
            ->andReturn(json_encode($responsePayload));

        $response = Mockery::mock(ResponseInterface::class);
        $response->shouldReceive('getBody')->andReturn($mockStream);

        $mockClient = Mockery::mock(ClientInterface::class);
        $mockClient->shouldReceive('sendRequest')
            ->once()
            ->with(mockedPostRequest($this->config->endpoint('mbway_init'), $requestPayload))
            ->andReturn($response);

        $ifthenpayGateway = new IfthenpayGateway($this->configArray, $mockClient);
        $mbwayPayment = $ifthenpayGateway->mbway()->initPayment('01', '0.50', '912345678', 'description');
        expect($mbwayPayment)->toBeInstanceOf(Mbway::class);
        expect($mbwayPayment->getAmount())->toBe('0.50');
        expect($mbwayPayment->getOrderId())->toBe('01');
        expect($mbwayPayment->getMobileNumber())->toBe('912345678');
        expect($mbwayPayment->getStatus())->toBe(Status::PENDING);
    });

    it('throws EndpointResponseException if invalid account', function () {

        $requestPayload = [
            'mbWayKey'        => 'ITP-000000',
            'orderId'    => '01',
            'amount'     => '0.50',
            'mobileNumber'      => '912345678',
            'email'  => null,
            'description' => 'description'
        ];

        $responsePayload = [
            'Amount'    => '',
            'OrderId'   => '',
            'Message'   => 'Error',
            'Status'    => MbwayService::INIT_STATUS_INVALID_ACCOUNT,
            'RequestId' => '',
        ];

        $mockStream = Mockery::mock(StreamInterface::class);
        $mockStream->shouldReceive('__toString')
            ->andReturn(json_encode($responsePayload));

        $response = Mockery::mock(ResponseInterface::class);
        $response->shouldReceive('getBody')->andReturn($mockStream);

        $mockClient = Mockery::mock(ClientInterface::class);
        $mockClient->shouldReceive('sendRequest')
            ->once()
            ->with(mockedPostRequest($this->config->endpoint('mbway_init'), $requestPayload))
            ->andReturn($response);

        $ifthenpayGateway = new IfthenpayGateway($this->configArray, $mockClient);
        $ifthenpayGateway->mbway()->initPayment('01', '0.50', '912345678', 'description');
    })->throws(Ifthenpay\PaymentGateway\Exception\EndpointResponseException::class, 'The MB WAY key is invalid');

    it('throws EndpointResponseException if error initializing request', function () {

        $requestPayload = [
            'mbWayKey'        => 'ITP-000000',
            'orderId'    => '01',
            'amount'     => '0.50',
            'mobileNumber'      => '912345678',
            'email'  => null,
            'description' => 'description'
        ];

        $responsePayload = [
            'Amount'    => '',
            'OrderId'   => '',
            'Message'   => 'Error',
            'Status'    => MbwayService::INIT_STATUS_ERROR,
            'RequestId' => '',
        ];

        $mockStream = Mockery::mock(StreamInterface::class);
        $mockStream->shouldReceive('__toString')
            ->andReturn(json_encode($responsePayload));

        $response = Mockery::mock(ResponseInterface::class);
        $response->shouldReceive('getBody')->andReturn($mockStream);

        $mockClient = Mockery::mock(ClientInterface::class);
        $mockClient->shouldReceive('sendRequest')
            ->once()
            ->with(mockedPostRequest($this->config->endpoint('mbway_init'), $requestPayload))
            ->andReturn($response);

        $ifthenpayGateway = new IfthenpayGateway($this->configArray, $mockClient);
        $ifthenpayGateway->mbway()->initPayment('01', '0.50', '912345678', 'description');
    })->throws(Ifthenpay\PaymentGateway\Exception\EndpointResponseException::class, 'Error initializing the request.');

    it('throws EndpointResponseException if incomplete request', function () {

        $requestPayload = [
            'mbWayKey'        => 'ITP-000000',
            'orderId'    => '01',
            'amount'     => '0.50',
            'mobileNumber'      => '912345678',
            'email'  => null,
            'description' => 'description'
        ];

        $responsePayload = [
            'Amount'    => '',
            'OrderId'   => '',
            'Message'   => 'Error',
            'Status'    => MbwayService::INIT_STATUS_INCOMPLETE,
            'RequestId' => '',
        ];

        $mockStream = Mockery::mock(StreamInterface::class);
        $mockStream->shouldReceive('__toString')
            ->andReturn(json_encode($responsePayload));

        $response = Mockery::mock(ResponseInterface::class);
        $response->shouldReceive('getBody')->andReturn($mockStream);

        $mockClient = Mockery::mock(ClientInterface::class);
        $mockClient->shouldReceive('sendRequest')
            ->once()
            ->with(mockedPostRequest($this->config->endpoint('mbway_init'), $requestPayload))
            ->andReturn($response);

        $ifthenpayGateway = new IfthenpayGateway($this->configArray, $mockClient);
        $ifthenpayGateway->mbway()->initPayment('01', '0.50', '912345678', 'description');
    })->throws(Ifthenpay\PaymentGateway\Exception\EndpointResponseException::class, 'The initialization request could not be completed.');

    it('throws EndpointResponseException if declined request', function () {

        $requestPayload = [
            'mbWayKey'        => 'ITP-000000',
            'orderId'    => '01',
            'amount'     => '0.50',
            'mobileNumber'      => '912345678',
            'email'  => null,
            'description' => 'description'
        ];

        $responsePayload = [
            'Amount'    => '',
            'OrderId'   => '',
            'Message'   => 'Error',
            'Status'    => MbwayService::INIT_STATUS_DECLINED,
            'RequestId' => '',
        ];

        $mockStream = Mockery::mock(StreamInterface::class);
        $mockStream->shouldReceive('__toString')
            ->andReturn(json_encode($responsePayload));

        $response = Mockery::mock(ResponseInterface::class);
        $response->shouldReceive('getBody')->andReturn($mockStream);

        $mockClient = Mockery::mock(ClientInterface::class);
        $mockClient->shouldReceive('sendRequest')
            ->once()
            ->with(mockedPostRequest($this->config->endpoint('mbway_init'), $requestPayload))
            ->andReturn($response);

        $ifthenpayGateway = new IfthenpayGateway($this->configArray, $mockClient);
        $ifthenpayGateway->mbway()->initPayment('01', '0.50', '912345678', 'description');
    })->throws(Ifthenpay\PaymentGateway\Exception\EndpointResponseException::class, 'Transaction declined by SIBS to the user.');

    it('throws EndpointResponseException if unexpected response', function () {

        $requestPayload = [
            'mbWayKey'        => 'ITP-000000',
            'orderId'    => '01',
            'amount'     => '0.50',
            'mobileNumber'      => '912345678',
            'email'  => null,
            'description' => 'description'
        ];

        $responsePayload = [
            'Amount'    => '',
            'OrderId'   => '',
            'Message'   => 'Error',
            'Status'    => 'UNKNOWN_STATUS',
            'RequestId' => '',
        ];

        $mockStream = Mockery::mock(StreamInterface::class);
        $mockStream->shouldReceive('__toString')
            ->andReturn(json_encode($responsePayload));

        $response = Mockery::mock(ResponseInterface::class);
        $response->shouldReceive('getBody')->andReturn($mockStream);

        $mockClient = Mockery::mock(ClientInterface::class);
        $mockClient->shouldReceive('sendRequest')
            ->once()
            ->with(mockedPostRequest($this->config->endpoint('mbway_init'), $requestPayload))
            ->andReturn($response);

        $ifthenpayGateway = new IfthenpayGateway($this->configArray, $mockClient);
        $ifthenpayGateway->mbway()->initPayment('01', '0.50', '912345678', 'description');
    })->throws(Ifthenpay\PaymentGateway\Exception\EndpointResponseException::class, 'Mbway payment request returned unexpected response.');
});

describe('[FEATURE] Mbway validateWebhook', function () {
    it('validates webhook successfully', function () {

        $ifthenpayGateway = new IfthenpayGateway($this->configArray);
        $mbwayService = $ifthenpayGateway->mbway();

        $webhookRequest = new Ifthenpay\PaymentGateway\RequestObj\WebhookRequest(...[
            'orderId'        => '01',
            'amount'         => '0.50',
            'transactionId'  => 'tid000000109',
            'antiPhishingKey' => $this->configArray['antiPhishingKey']
        ]);
        $payment = new Mbway('0.50', '01', 'tid000000109', '912345678', Status::PENDING, DateTools::getFutureDate(0, 0, 15));

        expect($mbwayService->validateWebhook($webhookRequest, $payment))->not->toThrow(Ifthenpay\PaymentGateway\Exception\WebhookValidationException::class);
    });

    it('throws WebhookValidationException if antiPhishingKey does not match', function () {
        $ifthenpayGateway = new IfthenpayGateway($this->configArray);
        $mbwayService = $ifthenpayGateway->mbway();

        $webhookRequest = new Ifthenpay\PaymentGateway\RequestObj\WebhookRequest(...[
            'orderId'        => '01',
            'amount'         => '0.50',
            'transactionId'  => 'tid000000109',
            'antiPhishingKey' => 'wrongkey'
        ]);
        $payment = new Mbway('0.50', '01', 'tid000000109', '912345678', Status::PENDING, DateTools::getFutureDate(0, 0, 15));

        expect(fn() => $mbwayService->validateWebhook($webhookRequest, $payment))->toThrow(Ifthenpay\PaymentGateway\Exception\WebhookValidationException::class, 'AntiPhishingKey does not match');
    });

    it('throws WebhookValidationException if orderId does not match', function () {
        $ifthenpayGateway = new IfthenpayGateway($this->configArray);
        $mbwayService = $ifthenpayGateway->mbway();

        $webhookRequest = new Ifthenpay\PaymentGateway\RequestObj\WebhookRequest(...[
            'orderId'        => '01',
            'amount'         => '0.50',
            'transactionId'  => 'tid000000109',
            'antiPhishingKey' => $this->configArray['antiPhishingKey']
        ]);
        $payment = new Mbway('0.50', '02', 'tid000000109', '912345678', Status::PENDING, DateTools::getFutureDate(0, 0, 15));

        expect(fn() => $mbwayService->validateWebhook($webhookRequest, $payment))->toThrow(Ifthenpay\PaymentGateway\Exception\WebhookValidationException::class, 'OrderId does not match');
    });
});


describe('[FEATURE] mbway registerWebhook', function () {
    it('registers webhook successfully with valid url', function () {

        $mockClient = mockClientRegisterWebhook();

        $ifthenpayGateway = new IfthenpayGateway($this->configArray, $mockClient);

        expect($ifthenpayGateway->mbway()->registerWebhook('https://example.com/webhook'))->not->toThrow(Ifthenpay\PaymentGateway\Exception\EndpointResponseException::class);
    });
    it('throws exception if url length exceeds 300 characters', function () {

        $ifthenpayGateway = new IfthenpayGateway($this->configArray);
        $result = $ifthenpayGateway->mbway()->registerWebhook('https://example.com/webhookwebhook11111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111webhook11111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111');
    })->throws(InvalidArgumentException::class, 'length must be equal or less than 300 characters.');
    it('throws exception if url not valid url string', function () {

        $ifthenpayGateway = new IfthenpayGateway($this->configArray);
        $result = $ifthenpayGateway->mbway()->registerWebhook('testurl');
    })->throws(InvalidArgumentException::class, 'must be a valid URL.');
});


describe('[FEATURE] mbway isPaid', function () {

    it('returns payment paid status as true', function () {
        $mockClient = mockClientIsPaidHttp(true);

        $ifthenpayGateway = new IfthenpayGateway($this->configArray, $mockClient);
        $mbwayPayment = new Mbway('0.50', '01', 'tid000000109', '912345678', Status::PENDING, DateTools::getFutureDate(0, 0, 15));
        $result = $ifthenpayGateway->mbway()->isPaid($mbwayPayment);
        expect($result)->toBeTrue();
    });
    it('returns payment paid status as false', function () {

        $mockClient = mockClientIsPaidHttp(false);

        $ifthenpayGateway = new IfthenpayGateway($this->configArray, $mockClient);
        $mbwayPayment = new Mbway('0.50', '01', 'tid000000109', '912345678', Status::PENDING, DateTools::getFutureDate(0, 0, 15));
        $result = $ifthenpayGateway->mbway()->isPaid($mbwayPayment);
        expect($result)->toBeFalse();
    });
});



describe('[FEATURE] mbway isExpired', function () {

    it('returns payment expired status as true', function () {

        $pastDate = DateTimeImmutable::createFromMutable(new \DateTime('now'));

        $ifthenpayGateway = new IfthenpayGateway($this->configArray);

        $payment = new Mbway('0.50', '01', 'tid000000109', '929999999', Status::PENDING, $pastDate);
        $result = $ifthenpayGateway->mbway()->isExpired($payment);
        expect($result)->toBeTrue();
    });
    it('returns payment expired status as false', function () {

        $futureDate = DateTimeImmutable::createFromMutable(new \DateTime('now + 1minute'));


        $ifthenpayGateway = new IfthenpayGateway($this->configArray);
        $payment = new Mbway('0.50', '01', 'tid000000109', '929999999', Status::PENDING, $futureDate);
        $result = $ifthenpayGateway->mbway()->isExpired($payment);
        expect($result)->toBeFalse();
    });
    it('returns payment expired status as false when expire not set', function () {

        $ifthenpayGateway = new IfthenpayGateway($this->configArray);
        $payment = new Mbway('0.50', '01', 'tid000000109', '929999999', Status::PENDING);
        $result = $ifthenpayGateway->mbway()->isExpired($payment);
        expect($result)->toBeFalse();
    });
});
