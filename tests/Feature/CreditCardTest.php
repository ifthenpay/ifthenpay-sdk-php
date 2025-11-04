<?php

use Ifthenpay\PaymentGateway\Config;
use Ifthenpay\PaymentGateway\Enums\Status;
use Ifthenpay\PaymentGateway\IfthenpayGateway;
use Ifthenpay\PaymentGateway\Model\CreditCard;
use Ifthenpay\PaymentGateway\Service\CreditCardService;
use Ifthenpay\PaymentGateway\Utils\DateTools;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

beforeEach(function () {

    $this->configArray = [
        'creditCard' => [
            'key'    => 'ITP-000000',
            'successUrl' => 'https://yourdomain.com/success',
            'errorUrl' => 'https://yourdomain.com/error',
            'cancelUrl' => 'https://yourdomain.com/cancel',
        ],
        'backofficeKey'   => '1111-1111-1111-1111',
        'antiPhishingKey' => '1234123412341234',
    ];

    $this->config = Config::fromArray($this->configArray);
});


describe('[FEATURE] Credit Card initPayment', function () {
    it('initializes payment successfully', function () {

        $requestPayload = [
            'orderId'   => '01',
            'amount'    => '0.50',
            'successUrl' => 'https://yourdomain.com/success',
            'errorUrl' => 'https://yourdomain.com/error',
            'cancelUrl' => 'https://yourdomain.com/cancel',
            'language'  => 'pt'
        ];

        $responsePayload = [
            'Message' => 'Success',
            'PaymentUrl' => 'https://paymentprovider/url/test',
            'RequestId'    => 'CmOc7XIjeLxy5Rlf1uQo',
            'Status'    => CreditCardService::INIT_STATUS_SUCCESS,
        ];

        $mockStream = Mockery::mock(StreamInterface::class);
        $mockStream->shouldReceive('__toString')
            ->andReturn(json_encode($responsePayload));

        $response = Mockery::mock(ResponseInterface::class);
        $response->shouldReceive('getBody')->andReturn($mockStream);

        $mockClient = Mockery::mock(ClientInterface::class);
        $mockClient->shouldReceive('sendRequest')
            ->once()
            ->with(mockedPostRequest($this->config->endpoint('creditcard_init') . $this->config->creditCardKey(), $requestPayload))
            ->andReturn($response);

        $ifthenpayGateway = new IfthenpayGateway($this->configArray, $mockClient);
        $creditCardPayment = $ifthenpayGateway->creditCard()->initPayment('01', '0.50');

        expect($creditCardPayment)->toBeInstanceOf(CreditCard::class);
        expect($creditCardPayment->getAmount())->toBe('0.50');
        expect($creditCardPayment->getOrderId())->toBe('01');
        expect($creditCardPayment->getPaymentUrl())->toBe('https://paymentprovider/url/test');
        expect($creditCardPayment->getTransactionId())->toBe('CmOc7XIjeLxy5Rlf1uQo');
        expect($creditCardPayment->getStatus())->toBe(Status::PENDING);
    });



    it('throws EndpointResponseException if invalid account', function () {

        $requestPayload = [
            'orderId'   => '01',
            'amount'    => '0.50',
            'successUrl' => 'https://yourdomain.com/success',
            'errorUrl' => 'https://yourdomain.com/error',
            'cancelUrl' => 'https://yourdomain.com/cancel',
            'language'  => 'pt'
        ];

        $responsePayload = [
            'Message' => 'Unauthorized request',
            'PaymentUrl' => '',
            'RequestId'    => '',
            'Status'    => CreditCardService::INIT_STATUS_ERROR,
        ];

        $mockStream = Mockery::mock(StreamInterface::class);
        $mockStream->shouldReceive('__toString')
            ->andReturn(json_encode($responsePayload));

        $response = Mockery::mock(ResponseInterface::class);
        $response->shouldReceive('getBody')->andReturn($mockStream);

        $mockClient = Mockery::mock(ClientInterface::class);
        $mockClient->shouldReceive('sendRequest')
            ->once()
            ->with(mockedPostRequest($this->config->endpoint('creditcard_init') . $this->config->creditCardKey(), $requestPayload))
            ->andReturn($response);


        $ifthenpayGateway = new IfthenpayGateway($this->configArray, $mockClient);
        $ifthenpayGateway->creditCard()->initPayment('01', '0.50');
    })->throws(Ifthenpay\PaymentGateway\Exception\EndpointResponseException::class, 'Error initializing the request.');



    it('throws EndpointResponseException if unexpected response', function () {

        $requestPayload = [
            'orderId'   => '01',
            'amount'    => '0.50',
            'successUrl' => 'https://yourdomain.com/success',
            'errorUrl' => 'https://yourdomain.com/error',
            'cancelUrl' => 'https://yourdomain.com/cancel',
            'language'  => 'pt'
        ];

        $responsePayload = [
            'Message' => '',
            'PaymentUrl' => '',
            'RequestId'    => '',
            'Status'    => '999',
        ];


        $mockStream = Mockery::mock(StreamInterface::class);
        $mockStream->shouldReceive('__toString')
            ->andReturn(json_encode($responsePayload));

        $response = Mockery::mock(ResponseInterface::class);
        $response->shouldReceive('getBody')->andReturn($mockStream);

        $mockClient = Mockery::mock(ClientInterface::class);
        $mockClient->shouldReceive('sendRequest')
            ->once()
            ->with(mockedPostRequest($this->config->endpoint('creditcard_init') . $this->config->creditCardKey(), $requestPayload))
            ->andReturn($response);


        $ifthenpayGateway = new IfthenpayGateway($this->configArray, $mockClient);
        $ifthenpayGateway->creditCard()->initPayment('01', '0.50');
    })->throws(Ifthenpay\PaymentGateway\Exception\EndpointResponseException::class, 'CreditCard payment request returned unexpected response.');
});



describe('[FEATURE] CreditCard validateWebhook', function () {
    it('validates webhook successfully', function () {

        $ifthenpayGateway = new IfthenpayGateway($this->configArray);
        $creditCardService = $ifthenpayGateway->creditCard();

        $webhookRequest = new Ifthenpay\PaymentGateway\RequestObj\WebhookRequest(...[
            'orderId'        => '01',
            'amount'         => '0.50',
            'transactionId'  => 'tid000000109',
            'antiPhishingKey' => $this->configArray['antiPhishingKey']
        ]);

        $payment = new CreditCard('0.50', '01', 'tid000000109', 'https://testurl.com', Status::PENDING, DateTools::getFutureDate(0, 0, 15));

        expect($creditCardService->validateWebhook($webhookRequest, $payment))->not->toThrow(Ifthenpay\PaymentGateway\Exception\WebhookValidationException::class);
    });



    it('throws WebhookValidationException if antiPhishingKey does not match', function () {
        $ifthenpayGateway = new IfthenpayGateway($this->configArray);
        $creditCardService = $ifthenpayGateway->creditCard();

        $webhookRequest = new Ifthenpay\PaymentGateway\RequestObj\WebhookRequest(...[
            'orderId'        => '01',
            'amount'         => '0.50',
            'transactionId'  => 'tid000000109',
            'antiPhishingKey' => 'wrongkey'
        ]);
        $payment = new CreditCard('0.50', '01', 'tid000000109', 'https://testurl.com', Status::PENDING, DateTools::getFutureDate(0, 0, 15));

        expect(fn() => $creditCardService->validateWebhook($webhookRequest, $payment))->toThrow(Ifthenpay\PaymentGateway\Exception\WebhookValidationException::class, 'AntiPhishingKey does not match');
    });



    it('throws WebhookValidationException if orderId does not match', function () {
        $ifthenpayGateway = new IfthenpayGateway($this->configArray);
        $creditCardService = $ifthenpayGateway->creditCard();

        $webhookRequest = new Ifthenpay\PaymentGateway\RequestObj\WebhookRequest(...[
            'orderId'        => '01',
            'amount'         => '0.50',
            'transactionId'  => 'tid000000109',
            'antiPhishingKey' => $this->configArray['antiPhishingKey']
        ]);
        $payment = new CreditCard('0.50', '02', 'tid000000109', 'https://testurl.com', Status::PENDING, DateTools::getFutureDate(0, 0, 15));

        expect(fn() => $creditCardService->validateWebhook($webhookRequest, $payment))->toThrow(Ifthenpay\PaymentGateway\Exception\WebhookValidationException::class, 'OrderId does not match');
    });
});



describe('[FEATURE] creditCard registerWebhook', function () {
    it('registers webhook successfully with valid url', function () {

        $mockClient = mockClientRegisterWebhook();

        $ifthenpayGateway = new IfthenpayGateway($this->configArray, $mockClient);

        expect($ifthenpayGateway->creditCard()->registerWebhook('https://example.com/webhook'))->not->toThrow(Ifthenpay\PaymentGateway\Exception\EndpointResponseException::class);
    });
    it('throws exception if url length exceeds 300 characters', function () {

        $ifthenpayGateway = new IfthenpayGateway($this->configArray);
        $result = $ifthenpayGateway->creditCard()->registerWebhook('https://example.com/webhookwebhook11111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111webhook11111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111');
    })->throws(InvalidArgumentException::class, 'length must be equal or less than 300 characters.');
    it('throws exception if url not valid url string', function () {

        $ifthenpayGateway = new IfthenpayGateway($this->configArray);
        $result = $ifthenpayGateway->creditCard()->registerWebhook('testurl');
    })->throws(InvalidArgumentException::class, 'must be a valid URL.');
});



describe('[FEATURE] creditCard isPaid', function () {

    it('returns payment paid status as true', function () {
        $mockClient = mockClientIsPaidHttp(true);

        $ifthenpayGateway = new IfthenpayGateway($this->configArray, $mockClient);
        $creditCardPayment = new CreditCard('0.50', '01', 'tid000000109', '912345678', Status::PENDING, DateTools::getFutureDate(0, 0, 15));
        $result = $ifthenpayGateway->creditCard()->isPaid($creditCardPayment);
        expect($result)->toBeTrue();
    });
    it('returns payment paid status as false', function () {

        $mockClient = mockClientIsPaidHttp(false);

        $ifthenpayGateway = new IfthenpayGateway($this->configArray, $mockClient);
        $creditCardPayment = new CreditCard('0.50', '01', 'tid000000109', '912345678', Status::PENDING, DateTools::getFutureDate(0, 0, 15));
        $result = $ifthenpayGateway->creditCard()->isPaid($creditCardPayment);
        expect($result)->toBeFalse();
    });
});



describe('[FEATURE] creditCard isExpired', function () {

    it('returns payment expired status as true', function () {

        $pastDate = DateTimeImmutable::createFromMutable(new \DateTime('now'));

        $ifthenpayGateway = new IfthenpayGateway($this->configArray);

        $creditCardPayment = new CreditCard('0.50', '01', 'tid000000109', 'https://exampleurl.com', Status::PENDING, $pastDate);
        $result = $ifthenpayGateway->creditCard()->isExpired($creditCardPayment);
        expect($result)->toBeTrue();
    });
    it('returns payment expired status as false', function () {

        $futureDate = DateTimeImmutable::createFromMutable(new \DateTime('now + 1minute'));


        $ifthenpayGateway = new IfthenpayGateway($this->configArray);
        $creditCardPayment = new CreditCard('0.50', '01', 'tid000000109', 'https://exampleurl.com', Status::PENDING, $futureDate);
        $result = $ifthenpayGateway->creditCard()->isExpired($creditCardPayment);
        expect($result)->toBeFalse();
    });
    it('returns payment expired status as false when expire not set', function () {

        $ifthenpayGateway = new IfthenpayGateway($this->configArray);
        $creditCardPayment = new CreditCard('0.50', '01', 'tid000000109', 'https://exampleurl.com', Status::PENDING);
        $result = $ifthenpayGateway->creditCard()->isExpired($creditCardPayment);
        expect($result)->toBeFalse();
    });
});
