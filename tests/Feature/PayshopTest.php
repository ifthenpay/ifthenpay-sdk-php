<?php

use Ifthenpay\PaymentGateway\Config;
use Ifthenpay\PaymentGateway\Enums\Status;
use Ifthenpay\PaymentGateway\IfthenpayGateway;
use Ifthenpay\PaymentGateway\Model\Payshop;
use Ifthenpay\PaymentGateway\Service\PayshopService;
use Ifthenpay\PaymentGateway\Utils\DateTools;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

beforeEach(function () {

    $this->configArray = [
        'payshop' => [
            'key'    => 'ITP-000000',
        ],
        'backofficeKey'   => '1111-1111-1111-1111',
        'antiPhishingKey' => '1234123412341234',
    ];

    $this->config = Config::fromArray($this->configArray);
});


describe('[FEATURE] Payshop initPayment', function () {
    it('initializes payment successfully', function () {

        $requestPayload = [
            'payshopkey' => $this->config->payshopKey(),
            'id'    => '01',
            'valor'     => '0.50',
            'validade'  => null
        ];

        $responsePayload = [
            "Code" => PayshopService::INIT_STATUS_SUCCESS,
            "Message" => "Success",
            "Reference" => "1111111111111",
            "RequestId" => "tid11111111111111111",
        ];

        $mockStream = Mockery::mock(StreamInterface::class);
        $mockStream->shouldReceive('__toString')
            ->andReturn(json_encode($responsePayload));

        $response = Mockery::mock(ResponseInterface::class);
        $response->shouldReceive('getBody')->andReturn($mockStream);

        $mockClient = Mockery::mock(ClientInterface::class);
        $mockClient->shouldReceive('sendRequest')
            ->once()
            ->with(mockedPostRequest($this->config->endpoint('payshop_init'), $requestPayload))
            ->andReturn($response);

        $ifthenpayGateway = new IfthenpayGateway($this->configArray, $mockClient);
        $payshopPayment = $ifthenpayGateway->payshop()->initPayment('01', '0.50');


        expect($payshopPayment)->toBeInstanceOf(Payshop::class);
        expect($payshopPayment->getAmount())->toBe('0.50');
        expect($payshopPayment->getOrderId())->toBe('01');
        expect($payshopPayment->getReference())->toBe('1111111111111');
        expect($payshopPayment->getTransactionId())->toBe('tid11111111111111111');
        expect($payshopPayment->getExpireDate())->toBe(null);
        expect($payshopPayment->getStatus())->toBe(Status::PENDING);
    });


    it('initializes payment successfully with deadline passed in method', function () {

        $requestPayload = [
            'payshopkey' => $this->config->payshopKey(),
            'id'    => '01',
            'valor'     => '0.50',
            'validade'  => DateTools::getFutureDate(3, 0, 0)->format('Ymd')
        ];

        $responsePayload = [
            "Code" => PayshopService::INIT_STATUS_SUCCESS,
            "Message" => "Success",
            "Reference" => "1111111111111",
            "RequestId" => "tid11111111111111111",
        ];

        $mockStream = Mockery::mock(StreamInterface::class);
        $mockStream->shouldReceive('__toString')
            ->andReturn(json_encode($responsePayload));

        $response = Mockery::mock(ResponseInterface::class);
        $response->shouldReceive('getBody')->andReturn($mockStream);

        $mockClient = Mockery::mock(ClientInterface::class);
        $mockClient->shouldReceive('sendRequest')
            ->once()
            ->with(mockedPostRequest($this->config->endpoint('payshop_init'), $requestPayload))
            ->andReturn($response);

        $ifthenpayGateway = new IfthenpayGateway($this->configArray, $mockClient);
        $payshopPayment = $ifthenpayGateway->payshop()->initPayment('01', '0.50', 3);


        expect($payshopPayment)->toBeInstanceOf(Payshop::class);
        expect($payshopPayment->getAmount())->toBe('0.50');
        expect($payshopPayment->getOrderId())->toBe('01');
        expect($payshopPayment->getReference())->toBe('1111111111111');
        expect($payshopPayment->getTransactionId())->toBe('tid11111111111111111');
        expect($payshopPayment->getExpireDate())->toEqual(DateTools::getFutureDate(4, 23, 59, true)); // sets plus one day (only for payshop)
        expect($payshopPayment->getStatus())->toBe(Status::PENDING);
    });


    it('initializes payment successfully with deadline passed from config', function () {

        $requestPayload = [
            'payshopkey' => $this->config->payshopKey(),
            'id'    => '01',
            'valor'     => '0.50',
            'validade'  => DateTools::getFutureDate(3, 0, 0)->format('Ymd')
        ];

        $responsePayload = [
            "Code" => PayshopService::INIT_STATUS_SUCCESS,
            "Message" => "Success",
            "Reference" => "1111111111111",
            "RequestId" => "tid11111111111111111",
        ];

        $mockStream = Mockery::mock(StreamInterface::class);
        $mockStream->shouldReceive('__toString')
            ->andReturn(json_encode($responsePayload));

        $response = Mockery::mock(ResponseInterface::class);
        $response->shouldReceive('getBody')->andReturn($mockStream);

        $mockClient = Mockery::mock(ClientInterface::class);
        $mockClient->shouldReceive('sendRequest')
            ->once()
            ->with(mockedPostRequest($this->config->endpoint('payshop_init'), $requestPayload))
            ->andReturn($response);


        $newConfigArray = $this->configArray;
        $newConfigArray['payshop']['daysToExpire'] = 3;

        $ifthenpayGateway = new IfthenpayGateway($newConfigArray, $mockClient);
        $payshopPayment = $ifthenpayGateway->payshop()->initPayment('01', '0.50');


        expect($payshopPayment)->toBeInstanceOf(Payshop::class);
        expect($payshopPayment->getAmount())->toBe('0.50');
        expect($payshopPayment->getOrderId())->toBe('01');
        expect($payshopPayment->getReference())->toBe('1111111111111');
        expect($payshopPayment->getTransactionId())->toBe('tid11111111111111111');
        expect($payshopPayment->getExpireDate())->toEqual(DateTools::getFutureDate(4, 23, 59, true)); // sets plus one day (only for payshop)
        expect($payshopPayment->getStatus())->toBe(Status::PENDING);
    });

    it('throws EndpointResponseException if invalid key', function () {


        $requestPayload = [
            'payshopkey' => $this->config->payshopKey(),
            'id'    => '01',
            'valor'     => '0.50',
            'validade'  => null
        ];

        $responsePayload = [
            "Code" => PayshopService::INIT_STATUS_INVALID_KEY,
            "Message" => "A payshopkey não é válida.",
            "Reference" => "",
            "RequestId" => "",
        ];


        $mockStream = Mockery::mock(StreamInterface::class);
        $mockStream->shouldReceive('__toString')
            ->andReturn(json_encode($responsePayload));

        $response = Mockery::mock(ResponseInterface::class);
        $response->shouldReceive('getBody')->andReturn($mockStream);

        $mockClient = Mockery::mock(ClientInterface::class);
        $mockClient->shouldReceive('sendRequest')
            ->once()
            ->with(mockedPostRequest($this->config->endpoint('payshop_init'), $requestPayload))
            ->andReturn($response);


        $ifthenpayGateway = new IfthenpayGateway($this->configArray, $mockClient);
        $ifthenpayGateway->payshop()->initPayment('01', '0.50');
    })->throws(Ifthenpay\PaymentGateway\Exception\EndpointResponseException::class, 'Invalid payshop account key.');


    it('throws EndpointResponseException if invalid params value', function () {
        // in case something passes through validation

        $requestPayload = [
            'payshopkey' => $this->config->payshopKey(),
            'id'    => '01',
            'valor'     => '0.50',
            'validade'  => null
        ];

        $responsePayload = [
            "Code" => PayshopService::INIT_STATUS_INVALID_PARAM_VALUE,
            "Message" => "Invalid parameter value.",
            "Reference" => "",
            "RequestId" => "",
        ];

        $mockStream = Mockery::mock(StreamInterface::class);
        $mockStream->shouldReceive('__toString')
            ->andReturn(json_encode($responsePayload));

        $response = Mockery::mock(ResponseInterface::class);
        $response->shouldReceive('getBody')->andReturn($mockStream);

        $mockClient = Mockery::mock(ClientInterface::class);
        $mockClient->shouldReceive('sendRequest')
            ->once()
            ->with(mockedPostRequest($this->config->endpoint('payshop_init'), $requestPayload))
            ->andReturn($response);

        $ifthenpayGateway = new IfthenpayGateway($this->configArray, $mockClient);
        $ifthenpayGateway->payshop()->initPayment('01', '0.50');
    })->throws(Ifthenpay\PaymentGateway\Exception\EndpointResponseException::class, 'Invalid parameter value.');



    it('throws EndpointResponseException if unexpected response missing fields', function () {

        $requestPayload = [
            'payshopkey' => $this->config->payshopKey(),
            'id'    => '01',
            'valor'     => '0.50',
            'validade'  => null
        ];

        $responsePayload = [
            "Code" => PayshopService::INIT_STATUS_INVALID_PARAM_VALUE,
            "Message" => "Invalid parameter value.",
            "Reference" => "",
        ];

        $mockStream = Mockery::mock(StreamInterface::class);
        $mockStream->shouldReceive('__toString')
            ->andReturn(json_encode($responsePayload));

        $response = Mockery::mock(ResponseInterface::class);
        $response->shouldReceive('getBody')->andReturn($mockStream);

        $mockClient = Mockery::mock(ClientInterface::class);
        $mockClient->shouldReceive('sendRequest')
            ->once()
            ->with(mockedPostRequest($this->config->endpoint('payshop_init'), $requestPayload))
            ->andReturn($response);

        $ifthenpayGateway = new IfthenpayGateway($this->configArray, $mockClient);
        $ifthenpayGateway->payshop()->initPayment('01', '0.50');
    })->throws(Ifthenpay\PaymentGateway\Exception\EndpointResponseException::class, 'Payshop payment request returned unexpected response missing fields.');



    it('throws EndpointResponseException if unexpected response unexpected error', function () {

        $requestPayload = [
            'payshopkey' => $this->config->payshopKey(),
            'id'    => '01',
            'valor'     => '0.50',
            'validade'  => null
        ];

        $responsePayload = [
            "Code" => '123455',
            "Message" => '',
            "Reference" => "",
            "RequestId" => "",

        ];

        $mockStream = Mockery::mock(StreamInterface::class);
        $mockStream->shouldReceive('__toString')
            ->andReturn(json_encode($responsePayload));

        $response = Mockery::mock(ResponseInterface::class);
        $response->shouldReceive('getBody')->andReturn($mockStream);

        $mockClient = Mockery::mock(ClientInterface::class);
        $mockClient->shouldReceive('sendRequest')
            ->once()
            ->with(mockedPostRequest($this->config->endpoint('payshop_init'), $requestPayload))
            ->andReturn($response);

        $ifthenpayGateway = new IfthenpayGateway($this->configArray, $mockClient);
        $ifthenpayGateway->payshop()->initPayment('01', '0.50');
    })->throws(Ifthenpay\PaymentGateway\Exception\EndpointResponseException::class, 'Payshop payment request returned unexpected response.');
});




describe('[FEATURE] Payshop validateWebhook', function () {
    it('validates webhook successfully', function () {

        $ifthenpayGateway = new IfthenpayGateway($this->configArray);
        $payshopService = $ifthenpayGateway->payshop();

        $webhookRequest = new Ifthenpay\PaymentGateway\RequestObj\WebhookRequest(...[
            'orderId'        => '01',
            'amount'         => '0.50',
            'transactionId'  => 'tid000000109',
            'antiPhishingKey' => $this->configArray['antiPhishingKey']
        ]);

        $payment = new Payshop('0.50', '01', 'tid000000109', '111111111111', Status::PENDING, null, DateTools::getTimeStamp());

        expect($payshopService->validateWebhook($webhookRequest, $payment))->not->toThrow(Ifthenpay\PaymentGateway\Exception\WebhookValidationException::class);
    });

    it('throws WebhookValidationException if antiPhishingKey does not match', function () {
        $ifthenpayGateway = new IfthenpayGateway($this->configArray);
        $payshopService = $ifthenpayGateway->payshop();

        $webhookRequest = new Ifthenpay\PaymentGateway\RequestObj\WebhookRequest(...[
            'orderId'        => '01',
            'amount'         => '0.50',
            'transactionId'  => 'tid000000109',
            'antiPhishingKey' => 'wrongkey'
        ]);

        $payment = new Payshop('0.50', '01', 'tid000000109', '111111111111', Status::PENDING, null, DateTools::getTimeStamp());

        expect(fn() => $payshopService->validateWebhook($webhookRequest, $payment))->toThrow(Ifthenpay\PaymentGateway\Exception\WebhookValidationException::class, 'AntiPhishingKey does not match');
    });

    it('throws WebhookValidationException if orderId does not match', function () {
        $ifthenpayGateway = new IfthenpayGateway($this->configArray);
        $payshopService = $ifthenpayGateway->payshop();

        $webhookRequest = new Ifthenpay\PaymentGateway\RequestObj\WebhookRequest(...[
            'orderId'        => '02',
            'amount'         => '0.50',
            'transactionId'  => 'tid000000109',
            'antiPhishingKey' => $this->configArray['antiPhishingKey']
        ]);

        $payment = new Payshop('0.50', '01', 'tid000000109', '111111111111', Status::PENDING, null, DateTools::getTimeStamp());

        expect(fn() => $payshopService->validateWebhook($webhookRequest, $payment))->toThrow(Ifthenpay\PaymentGateway\Exception\WebhookValidationException::class, 'OrderId does not match');
    });

    it('throws WebhookValidationException if amount does not match', function () {
        $ifthenpayGateway = new IfthenpayGateway($this->configArray);
        $payshopService = $ifthenpayGateway->payshop();

        $webhookRequest = new Ifthenpay\PaymentGateway\RequestObj\WebhookRequest(...[
            'orderId'        => '01',
            'amount'         => '0.20',
            'transactionId'  => 'tid000000109',
            'antiPhishingKey' => $this->configArray['antiPhishingKey']
        ]);

        $payment = new Payshop('0.50', '01', 'tid000000109', '111111111111', Status::PENDING, null, DateTools::getTimeStamp());

        expect(fn() => $payshopService->validateWebhook($webhookRequest, $payment))->toThrow(Ifthenpay\PaymentGateway\Exception\WebhookValidationException::class, 'Amount does not match');
    });

    it('throws WebhookValidationException if transaction does not match', function () {
        $ifthenpayGateway = new IfthenpayGateway($this->configArray);
        $payshopService = $ifthenpayGateway->payshop();

        $webhookRequest = new Ifthenpay\PaymentGateway\RequestObj\WebhookRequest(...[
            'orderId'        => '01',
            'amount'         => '0.50',
            'transactionId'  => 'tid000000110',
            'antiPhishingKey' => $this->configArray['antiPhishingKey']
        ]);

        $payment = new Payshop('0.50', '01', 'tid000000109', '111111111111', Status::PENDING, null, DateTools::getTimeStamp());

        expect(fn() => $payshopService->validateWebhook($webhookRequest, $payment))->toThrow(Ifthenpay\PaymentGateway\Exception\WebhookValidationException::class, 'TransactionId does not match');
    });
});


describe('[FEATURE] payshop registerWebhook', function () {
    it('registers webhook successfully with valid url', function () {

        $mockClient = mockClientRegisterWebhook();

        $ifthenpayGateway = new IfthenpayGateway($this->configArray, $mockClient);

        expect($ifthenpayGateway->payshop()->registerWebhook('https://example.com/webhook'))->not->toThrow(Ifthenpay\PaymentGateway\Exception\EndpointResponseException::class);
    });
    it('throws exception if url length exceeds 300 characters', function () {

        $ifthenpayGateway = new IfthenpayGateway($this->configArray);
        $result = $ifthenpayGateway->payshop()->registerWebhook('https://example.com/' . str_repeat('a', 290));
    })->throws(InvalidArgumentException::class, 'length must be equal or less than 300 characters.');
    it('throws exception if url not valid url string', function () {

        $ifthenpayGateway = new IfthenpayGateway($this->configArray);
        $result = $ifthenpayGateway->payshop()->registerWebhook('testurl');
    })->throws(InvalidArgumentException::class, 'must be a valid URL.');
});


describe('[FEATURE] payshop isPaid', function () {

    it('returns payment paid status as true', function () {
        $mockClient = mockClientIsPaidHttp(true);

        $ifthenpayGateway = new IfthenpayGateway($this->configArray, $mockClient);

        $payment = new Payshop('0.50', '01', 'tid000000109', '111111111111', Status::PENDING, null, DateTools::getTimeStamp());

        $result = $ifthenpayGateway->payshop()->isPaid($payment);
        expect($result)->toBeTrue();
    });
    it('returns payment paid status as false', function () {

        $mockClient = mockClientIsPaidHttp(false);

        $ifthenpayGateway = new IfthenpayGateway($this->configArray, $mockClient);
        $payment = new Payshop('0.50', '01', 'tid000000109', '111111111111', Status::PENDING, null, DateTools::getTimeStamp());
        $result = $ifthenpayGateway->payshop()->isPaid($payment);
        expect($result)->toBeFalse();
    });
});



describe('[FEATURE] payshop isExpired', function () {

    it('returns payment expired status as true', function () {

        $pastDate = DateTimeImmutable::createFromMutable(new \DateTime('now'));

        $ifthenpayGateway = new IfthenpayGateway($this->configArray);

        $payment = new Payshop('0.50', '01', 'tid000000109', '111111111111', Status::PENDING, $pastDate);
        $result = $ifthenpayGateway->payshop()->isExpired($payment);
        expect($result)->toBeTrue();
    });
    it('returns payment expired status as false', function () {

        $futureDate = DateTimeImmutable::createFromMutable(new \DateTime('now + 1minute'));

        $ifthenpayGateway = new IfthenpayGateway($this->configArray);
        $payment = new Payshop('0.50', '01', 'tid000000109', '111111111111', Status::PENDING, $futureDate);
        $result = $ifthenpayGateway->payshop()->isExpired($payment);
        expect($result)->toBeFalse();
    });
    it('returns payment expired status as false when expire not set', function () {

        $ifthenpayGateway = new IfthenpayGateway($this->configArray);
        $payment = new payshop('0.50', '01', '12345', '000000109', Status::PENDING);
        $result = $ifthenpayGateway->payshop()->isExpired($payment);
        expect($result)->toBeFalse();
    });
});
