<?php

use Ifthenpay\PaymentGateway\Config;
use Ifthenpay\PaymentGateway\Enums\Status;
use Ifthenpay\PaymentGateway\IfthenpayGateway;
use Ifthenpay\PaymentGateway\Model\Pix;
use Ifthenpay\PaymentGateway\Service\PixService;
use Ifthenpay\PaymentGateway\Utils\DateTools;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

beforeEach(function () {

    $this->configArray = [
        'pix' => [
            'key'    => 'ITP-000000',
        ],
        'backofficeKey'   => '1111-1111-1111-1111',
        'antiPhishingKey' => '1234123412341234',
    ];

    $this->config = Config::fromArray($this->configArray);
});


describe('[FEATURE] Pix initPayment', function () {
    it('initializes payment successfully', function () {

        $requestPayload = [
            'orderId'    => '01',
            'amount'     => '0.50',
            'customerCPF' => '111.111.111-11',
            'customerName' => 'Example Name',
            'customerEmail'  => 'example@mail.com',
            'customerPhone'      => '912345678',
            'redirectUrl'  => 'https://example.com/redirect',
            'description' => 'description'
        ];

        $responsePayload = [
            'message'     => 'Success',
            'paymentUrl'  => 'https://example-pix.com/payment/123456',
            'qrCodeValue' => 'example-qrcode-value',
            'requestId'   => 'tid123456',
            'status'      => PixService::INIT_STATUS_SUCCESS,
        ];

        $mockStream = Mockery::mock(StreamInterface::class);
        $mockStream->shouldReceive('__toString')
            ->andReturn(json_encode($responsePayload));

        $response = Mockery::mock(ResponseInterface::class);
        $response->shouldReceive('getBody')->andReturn($mockStream);

        $mockClient = Mockery::mock(ClientInterface::class);
        $mockClient->shouldReceive('sendRequest')
            ->once()
            ->with(mockedPostRequest($this->config->endpoint('pix_init') . $this->config->pixKey(), $requestPayload))
            ->andReturn($response);

        $ifthenpayGateway = new IfthenpayGateway($this->configArray, $mockClient);
        $pixPayment = $ifthenpayGateway->pix()->initPayment('01', '0.50', '111.111.111-11', 'Example Name', 'example@mail.com', '912345678', 'https://example.com/redirect', 'description');


        expect($pixPayment)->toBeInstanceOf(Pix::class);
        expect($pixPayment->getAmount())->toBe('0.50');
        expect($pixPayment->getOrderId())->toBe('01');
        expect($pixPayment->getMobileNumber())->toBe('912345678');
        expect($pixPayment->getEmail())->toBe('example@mail.com');
        expect($pixPayment->getPaymentUrl())->toBe('https://example-pix.com/payment/123456');
        expect($pixPayment->getQrCode())->toBe('example-qrcode-value');
        expect($pixPayment->getTransactionId())->toBe('tid123456');
        expect($pixPayment->getExpireDate())->toBe(null);
        expect($pixPayment->getStatus())->toBe(Status::PENDING);
    });

    it('throws EndpointResponseException if error initializing request', function () {

        $requestPayload = [
            'orderId'    => '01',
            'amount'     => '0.50',
            'customerCPF' => '111.111.111-11',
            'customerName' => 'Example Name',
            'customerEmail'  => 'example@mail.com',
            'customerPhone'      => '912345678',
            'redirectUrl'  => 'https://example.com/redirect',
            'description' => 'description'
        ];

        $responsePayload = [
            'message'     => 'Unauthorized request',
            'paymentUrl'  => '',
            'qrCodeValue' => '',
            'requestId'   => '',
            'status'      => PixService::INIT_STATUS_ERROR,
        ];

        $mockStream = Mockery::mock(StreamInterface::class);
        $mockStream->shouldReceive('__toString')
            ->andReturn(json_encode($responsePayload));

        $response = Mockery::mock(ResponseInterface::class);
        $response->shouldReceive('getBody')->andReturn($mockStream);

        $mockClient = Mockery::mock(ClientInterface::class);
        $mockClient->shouldReceive('sendRequest')
            ->once()
            ->with(mockedPostRequest($this->config->endpoint('pix_init') . $this->config->pixKey(), $requestPayload))
            ->andReturn($response);

        $ifthenpayGateway = new IfthenpayGateway($this->configArray, $mockClient);
        $ifthenpayGateway->pix()->initPayment('01', '0.50', '111.111.111-11', 'Example Name', 'example@mail.com', '912345678', 'https://example.com/redirect', 'description');
    })->throws(Ifthenpay\PaymentGateway\Exception\EndpointResponseException::class, 'Error initializing the request.');

    it('throws EndpointResponseException if unexpected response', function () {

        $requestPayload = [
            'orderId'    => '01',
            'amount'     => '0.50',
            'customerCPF' => '111.111.111-11',
            'customerName' => 'Example Name',
            'customerEmail'  => 'example@mail.com',
            'customerPhone'      => '912345678',
            'redirectUrl'  => 'https://example.com/redirect',
            'description' => 'description'
        ];

        $responsePayload = [
            'message'     => '',
        ];

        $mockStream = Mockery::mock(StreamInterface::class);
        $mockStream->shouldReceive('__toString')
            ->andReturn(json_encode($responsePayload));

        $response = Mockery::mock(ResponseInterface::class);
        $response->shouldReceive('getBody')->andReturn($mockStream);

        $mockClient = Mockery::mock(ClientInterface::class);
        $mockClient->shouldReceive('sendRequest')
            ->once()
            ->with(mockedPostRequest($this->config->endpoint('pix_init') . $this->config->pixKey(), $requestPayload))
            ->andReturn($response);

        $ifthenpayGateway = new IfthenpayGateway($this->configArray, $mockClient);
        $ifthenpayGateway->pix()->initPayment('01', '0.50', '111.111.111-11', 'Example Name', 'example@mail.com', '912345678', 'https://example.com/redirect', 'description');
    })->throws(Ifthenpay\PaymentGateway\Exception\EndpointResponseException::class, 'Pix payment request returned unexpected response.');
});

describe('[FEATURE] Pix validateWebhook', function () {
    it('validates webhook successfully', function () {

        $ifthenpayGateway = new IfthenpayGateway($this->configArray);
        $pixService = $ifthenpayGateway->pix();

        $webhookRequest = new Ifthenpay\PaymentGateway\RequestObj\WebhookRequest(...[
            'orderId'        => '01',
            'amount'         => '0.50',
            'transactionId'  => 'tid000000109',
            'antiPhishingKey' => $this->configArray['antiPhishingKey']
        ]);

        $payment = new Pix('0.50', '01', 'tid000000109', '929999999', 'example@mail.com', 'https://example-pix.com/payment/123456', 'example-qrcode-value', Status::PENDING, DateTools::getFutureDate(0, 0, 15), DateTools::getTimeStamp());

        expect($pixService->validateWebhook($webhookRequest, $payment))->not->toThrow(Ifthenpay\PaymentGateway\Exception\WebhookValidationException::class);
    });

    it('throws WebhookValidationException if antiPhishingKey does not match', function () {
        $ifthenpayGateway = new IfthenpayGateway($this->configArray);
        $pixService = $ifthenpayGateway->pix();

        $webhookRequest = new Ifthenpay\PaymentGateway\RequestObj\WebhookRequest(...[
            'orderId'        => '01',
            'amount'         => '0.50',
            'transactionId'  => 'tid000000109',
            'antiPhishingKey' => 'wrongkey'
        ]);
        $payment = new Pix('0.50', '01', 'tid000000109', '929999999', 'example@mail.com', 'https://example-pix.com/payment/123456', 'example-qrcode-value', Status::PENDING, DateTools::getFutureDate(0, 0, 15), DateTools::getTimeStamp());

        expect(fn() => $pixService->validateWebhook($webhookRequest, $payment))->toThrow(Ifthenpay\PaymentGateway\Exception\WebhookValidationException::class, 'AntiPhishingKey does not match');
    });

    it('throws WebhookValidationException if orderId does not match', function () {
        $ifthenpayGateway = new IfthenpayGateway($this->configArray);
        $pixService = $ifthenpayGateway->pix();

        $webhookRequest = new Ifthenpay\PaymentGateway\RequestObj\WebhookRequest(...[
            'orderId'        => '01',
            'amount'         => '0.50',
            'transactionId'  => 'tid000000109',
            'antiPhishingKey' => $this->configArray['antiPhishingKey']
        ]);
        $payment = new Pix('0.50', '02', 'tid000000109', '929999999', 'example@mail.com', 'https://example-pix.com/payment/123456', 'example-qrcode-value', Status::PENDING, DateTools::getFutureDate(0, 0, 15), DateTools::getTimeStamp());

        expect(fn() => $pixService->validateWebhook($webhookRequest, $payment))->toThrow(Ifthenpay\PaymentGateway\Exception\WebhookValidationException::class, 'OrderId does not match');
    });

    it('throws WebhookValidationException if amount does not match', function () {
        $ifthenpayGateway = new IfthenpayGateway($this->configArray);
        $pixService = $ifthenpayGateway->pix();

        $webhookRequest = new Ifthenpay\PaymentGateway\RequestObj\WebhookRequest(...[
            'orderId'        => '01',
            'amount'         => '0.50',
            'transactionId'  => 'tid000000109',
            'antiPhishingKey' => $this->configArray['antiPhishingKey']
        ]);
        $payment = new Pix('1.00', '01', 'tid000000109', '929999999', 'example@mail.com', 'https://example-pix.com/payment/123456', 'example-qrcode-value', Status::PENDING, DateTools::getFutureDate(0, 0, 15), DateTools::getTimeStamp());

        expect(fn() => $pixService->validateWebhook($webhookRequest, $payment))->toThrow(Ifthenpay\PaymentGateway\Exception\WebhookValidationException::class, 'Amount does not match');
    });

    it('throws WebhookValidationException if transaction does not match', function () {
        $ifthenpayGateway = new IfthenpayGateway($this->configArray);
        $pixService = $ifthenpayGateway->pix();

        $webhookRequest = new Ifthenpay\PaymentGateway\RequestObj\WebhookRequest(...[
            'orderId'        => '01',
            'amount'         => '0.50',
            'transactionId'  => 'tid000000109',
            'antiPhishingKey' => $this->configArray['antiPhishingKey']
        ]);
        $payment = new Pix('0.50', '01', 'tid000000110', '929999999', 'example@mail.com', 'https://example-pix.com/payment/123456', 'example-qrcode-value', Status::PENDING, DateTools::getFutureDate(0, 0, 15), DateTools::getTimeStamp());

        expect(fn() => $pixService->validateWebhook($webhookRequest, $payment))->toThrow(Ifthenpay\PaymentGateway\Exception\WebhookValidationException::class, 'TransactionId does not match');
    });
});


describe('[FEATURE] pix registerWebhook', function () {
    it('registers webhook successfully with valid url', function () {

        $mockClient = mockClientRegisterWebhook();

        $ifthenpayGateway = new IfthenpayGateway($this->configArray, $mockClient);

        expect($ifthenpayGateway->pix()->registerWebhook('https://example.com/webhook'))->not->toThrow(Ifthenpay\PaymentGateway\Exception\EndpointResponseException::class);
    });
    it('throws exception if url length exceeds 300 characters', function () {

        $ifthenpayGateway = new IfthenpayGateway($this->configArray);
        $result = $ifthenpayGateway->pix()->registerWebhook('https://example.com/' . str_repeat('a', 290));
    })->throws(InvalidArgumentException::class, 'length must be equal or less than 300 characters.');
    it('throws exception if url not valid url string', function () {

        $ifthenpayGateway = new IfthenpayGateway($this->configArray);
        $result = $ifthenpayGateway->pix()->registerWebhook('testurl');
    })->throws(InvalidArgumentException::class, 'must be a valid URL.');
});


describe('[FEATURE] pix isPaid', function () {

    it('returns payment paid status as true', function () {
        $mockClient = mockClientIsPaidHttp(true);

        $ifthenpayGateway = new IfthenpayGateway($this->configArray, $mockClient);
        $payment = new Pix('0.50', '01', 'tid000000110', '929999999', 'example@mail.com', 'https://example-pix.com/payment/123456', 'example-qrcode-value', Status::PENDING, DateTools::getFutureDate(0, 0, 15), DateTools::getTimeStamp());
        $result = $ifthenpayGateway->pix()->isPaid($payment);
        expect($result)->toBeTrue();
    });
    it('returns payment paid status as false', function () {

        $mockClient = mockClientIsPaidHttp(false);

        $ifthenpayGateway = new IfthenpayGateway($this->configArray, $mockClient);
        $payment = new Pix('0.50', '01', 'tid000000110', '929999999', 'example@mail.com', 'https://example-pix.com/payment/123456', 'example-qrcode-value', Status::PENDING, DateTools::getFutureDate(0, 0, 15), DateTools::getTimeStamp());
        $result = $ifthenpayGateway->pix()->isPaid($payment);
        expect($result)->toBeFalse();
    });
});



describe('[FEATURE] pix isExpired', function () {

    it('returns payment expired status as true', function () {

        $pastDate = DateTimeImmutable::createFromMutable(new \DateTime('now'));

        $ifthenpayGateway = new IfthenpayGateway($this->configArray);

        $payment = new Pix('0.50', '01', 'tid000000110', '929999999', 'example@mail.com', 'https://example-pix.com/payment/123456', 'example-qrcode-value', Status::PENDING, $pastDate);
        $result = $ifthenpayGateway->pix()->isExpired($payment);
        expect($result)->toBeTrue();
    });
    it('returns payment expired status as false', function () {

        $futureDate = DateTimeImmutable::createFromMutable(new \DateTime('now + 1minute'));

        $ifthenpayGateway = new IfthenpayGateway($this->configArray);
        $payment = new Pix('0.50', '01', 'tid000000110', '929999999', 'example@mail.com', 'https://example-pix.com/payment/123456', 'example-qrcode-value', Status::PENDING, $futureDate);
        $result = $ifthenpayGateway->pix()->isExpired($payment);
        expect($result)->toBeFalse();
    });
    it('returns payment expired status as false when expire not set', function () {

        $ifthenpayGateway = new IfthenpayGateway($this->configArray);
        $payment = new Pix('0.50', '01', 'tid000000110', '929999999', 'example@mail.com', 'https://example-pix.com/payment/123456', 'example-qrcode-value', Status::PENDING);
        $result = $ifthenpayGateway->pix()->isExpired($payment);
        expect($result)->toBeFalse();
    });
});
