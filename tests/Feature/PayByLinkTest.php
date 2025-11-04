<?php

use Ifthenpay\PaymentGateway\Config;
use Ifthenpay\PaymentGateway\Enums\MethodCode;
use Ifthenpay\PaymentGateway\Enums\Status;
use Ifthenpay\PaymentGateway\IfthenpayGateway;
use Ifthenpay\PaymentGateway\Model\PayByLink;
use Ifthenpay\PaymentGateway\Utils\DateTools;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

beforeEach(function () {

    $this->configArray = [
        'payByLink' => [
            'key'    => 'AITP-000000',
            'methodAccounts' => [
                '11202'  => '114',
                'MBWAY'  => 'ITP-000000',
                'PAYSHOP' => 'ITP-000000',
                'CCARD'  => 'ITP-000000',
                'COFIDIS' => 'ITP-000000',
                'GOOGLE' => 'ITP-000000',
                'APPLE'  => 'ITP-000000',
                'PIX'    => 'ITP-000000',
            ],
            'defaultMethod' => 'MB',
            'daysToExpire' => 5,
            'successUrl' => 'https://youraddress.com/sucess.php',
            'errorUrl' => 'https://youraddress.com/error.php',
            'cancelUrl' => 'https://youraddress.com/cancel.php',
            'btnCloseUrl' => 'https://youraddress.com',
            'btnCloseLabel' => 'Close',
        ],


        'backofficeKey'   => '1111-1111-1111-1111',
        'antiPhishingKey' => '1234123412341234',
    ];

    $this->config = Config::fromArray($this->configArray);
});


describe('[FEATURE] PayByLink initPayment', function () {
    it('initializes payment successfully', function () {

        $methodAcountsArr = $this->config->payByLinkMethodAccounts();
        $methodAccountStr = implode(';', array_map(
            fn($k, $v) => "$k|$v",
            array_keys($methodAcountsArr),
            $methodAcountsArr
        ));


        $requestPayload = [
            'id'         => '01',
            'amount'          => '0.50',
            'description'     => null,
            'accounts'  => $methodAccountStr,
            'selected_method'   => '1',
            'expiredate'    => DateTools::getFutureDate(5)->format('Ymd'),
            'otp' => 'false',
            'successUrl'      => $this->config->payByLinkSuccessUrl() . '?tid=[TRANSACTIONID]',
            'errorUrl'        => $this->config->payByLinkErrorUrl(),
            'cancelUrl'       => $this->config->payByLinkCancelUrl(),
            'btnCloseLabel' => $this->config->payByLinkBtnCloseLabel(),
            'btnCloseUrl'  => $this->config->payByLinkBtnCloseUrl(),
            'language'        => 'pt',
        ];

        $responsePayload = [
            'PinCode'     => '11111111111',
            'PinpayUrl'  => 'https://example-paybylink.com/payment/111111',
            'RedirectUrl'  => 'https://example-paybylink.com/redirect/111111',
        ];

        $mockStream = Mockery::mock(StreamInterface::class);
        $mockStream->shouldReceive('__toString')
            ->andReturn(json_encode($responsePayload));

        $response = Mockery::mock(ResponseInterface::class);
        $response->shouldReceive('getBody')->andReturn($mockStream);

        $mockClient = Mockery::mock(ClientInterface::class);
        $mockClient->shouldReceive('sendRequest')
            ->once()
            ->with(mockedPostRequest($this->config->endpoint('paybylink_init') . $this->config->payByLinkKey(), $requestPayload))
            ->andReturn($response);

        $ifthenpayGateway = new IfthenpayGateway($this->configArray, $mockClient);
        $paybylinkPayment = $ifthenpayGateway->payByLink()->initPayment('01', '0.50');



        expect($paybylinkPayment)->toBeInstanceOf(PayByLink::class);
        expect($paybylinkPayment->getAmount())->toBe('0.50');
        expect($paybylinkPayment->getOrderId())->toBe('01');
        expect($paybylinkPayment->getPinCode())->toBe('11111111111');
        expect($paybylinkPayment->getPaymentUrl())->toBe('https://example-paybylink.com/payment/111111');
        expect($paybylinkPayment->getExpireDate())->toBeInstanceOf(DateTimeImmutable::class);
        expect($paybylinkPayment->getExpireDate())->toEqual(DateTools::getFutureDate(5, 23, 59, true));
        expect($paybylinkPayment->getStatus())->toBe(Status::PENDING);
    });

    it('throws EndpointResponseException if invalid gateway key', function () {

        $methodAcountsArr = $this->config->payByLinkMethodAccounts();
        $methodAccountStr = implode(';', array_map(
            fn($k, $v) => "$k|$v",
            array_keys($methodAcountsArr),
            $methodAcountsArr
        ));


        $requestPayload = [
            'id'         => '01',
            'amount'          => '0.50',
            'description'     => null,
            'accounts'  => $methodAccountStr,
            'selected_method'   => '1',
            'expiredate'    => DateTools::getFutureDate(5)->format('Ymd'),
            'otp' => 'false',
            'successUrl'      => $this->config->payByLinkSuccessUrl() . '?tid=[TRANSACTIONID]',
            'errorUrl'        => $this->config->payByLinkErrorUrl(),
            'cancelUrl'       => $this->config->payByLinkCancelUrl(),
            'btnCloseLabel' => $this->config->payByLinkBtnCloseLabel(),
            'btnCloseUrl'  => $this->config->payByLinkBtnCloseUrl(),
            'language'        => 'pt',
        ];

        $responsePayload = [
            'PinCode'     => null,
            'PinpayUrl'  => null,
            'RedirectUrl'  => null,
        ];



        $mockStream = Mockery::mock(StreamInterface::class);
        $mockStream->shouldReceive('__toString')
            ->andReturn(json_encode($responsePayload));

        $response = Mockery::mock(ResponseInterface::class);
        $response->shouldReceive('getBody')->andReturn($mockStream);

        $mockClient = Mockery::mock(ClientInterface::class);
        $mockClient->shouldReceive('sendRequest')
            ->once()
            ->with(mockedPostRequest($this->config->endpoint('paybylink_init') . $this->config->payByLinkKey(), $requestPayload))
            ->andReturn($response);


        $ifthenpayGateway = new IfthenpayGateway($this->configArray, $mockClient);
        $ifthenpayGateway->payByLink()->initPayment('01', '0.50');
    })->throws(Ifthenpay\PaymentGateway\Exception\EndpointResponseException::class, 'PayByLink payment request returned null values. Possible invalid key, amount, or orderId.');

    it('throws EndpointResponseException if unexpected response', function () {

        $methodAcountsArr = $this->config->payByLinkMethodAccounts();
        $methodAccountStr = implode(';', array_map(
            fn($k, $v) => "$k|$v",
            array_keys($methodAcountsArr),
            $methodAcountsArr
        ));


        $requestPayload = [
            'id'         => '01',
            'amount'          => '0.50',
            'description'     => null,
            'accounts'  => $methodAccountStr,
            'selected_method'   => '1',
            'expiredate'    => DateTools::getFutureDate(5)->format('Ymd'),
            'otp' => 'false',
            'successUrl'      => $this->config->payByLinkSuccessUrl() . '?tid=[TRANSACTIONID]',
            'errorUrl'        => $this->config->payByLinkErrorUrl(),
            'cancelUrl'       => $this->config->payByLinkCancelUrl(),
            'btnCloseLabel' => $this->config->payByLinkBtnCloseLabel(),
            'btnCloseUrl'  => $this->config->payByLinkBtnCloseUrl(),
            'language'        => 'pt',
        ];

        $responsePayload = [
            'PinCode'     => null,
            'PinpayUrl'  => null,
        ];

        $mockStream = Mockery::mock(StreamInterface::class);
        $mockStream->shouldReceive('__toString')
            ->andReturn(json_encode($responsePayload));

        $response = Mockery::mock(ResponseInterface::class);
        $response->shouldReceive('getBody')->andReturn($mockStream);

        $mockClient = Mockery::mock(ClientInterface::class);
        $mockClient->shouldReceive('sendRequest')
            ->once()
            ->with(mockedPostRequest($this->config->endpoint('paybylink_init') . $this->config->payByLinkKey(), $requestPayload))
            ->andReturn($response);


        $ifthenpayGateway = new IfthenpayGateway($this->configArray, $mockClient);
        $ifthenpayGateway->payByLink()->initPayment('01', '0.50');
    })->throws(Ifthenpay\PaymentGateway\Exception\EndpointResponseException::class, 'PayByLink payment request returned unexpected response.');
});

describe('[FEATURE] PayByLink validateWebhook', function () {
    it('validates webhook successfully', function () {

        $ifthenpayGateway = new IfthenpayGateway($this->configArray);
        $paybylinkService = $ifthenpayGateway->paybylink();

        $webhookRequest = new Ifthenpay\PaymentGateway\RequestObj\WebhookRequest(...[
            'orderId'        => '01',
            'amount'         => '0.50',
            'transactionId'  => 'tid000000109',
            'antiPhishingKey' => $this->configArray['antiPhishingKey']
        ]);

        $payment = new PayByLink('0.50', '01', '1111111111', 'https://example-paybylink.com/payment/123456', Status::PENDING);

        expect($paybylinkService->validateWebhook($webhookRequest, $payment))->not->toThrow(Ifthenpay\PaymentGateway\Exception\WebhookValidationException::class);
    });

    it('throws WebhookValidationException if antiPhishingKey does not match', function () {
        $ifthenpayGateway = new IfthenpayGateway($this->configArray);
        $paybylinkService = $ifthenpayGateway->paybylink();

        $webhookRequest = new Ifthenpay\PaymentGateway\RequestObj\WebhookRequest(...[
            'orderId'        => '01',
            'amount'         => '0.50',
            'transactionId'  => 'tid000000109',
            'antiPhishingKey' => 'wrongkey'
        ]);
        $payment = new PayByLink('0.50', '01', '1111111111', 'https://example-paybylink.com/payment/123456', Status::PENDING);

        expect(fn() => $paybylinkService->validateWebhook($webhookRequest, $payment))->toThrow(Ifthenpay\PaymentGateway\Exception\WebhookValidationException::class, 'AntiPhishingKey does not match');
    });

    it('throws WebhookValidationException if orderId does not match', function () {
        $ifthenpayGateway = new IfthenpayGateway($this->configArray);
        $paybylinkService = $ifthenpayGateway->paybylink();

        $webhookRequest = new Ifthenpay\PaymentGateway\RequestObj\WebhookRequest(...[
            'orderId'        => '02',
            'amount'         => '0.50',
            'transactionId'  => 'tid000000109',
            'antiPhishingKey' => $this->configArray['antiPhishingKey']
        ]);
        $payment = new PayByLink('0.50', '01', '1111111111', 'https://example-paybylink.com/payment/123456', Status::PENDING);

        expect(fn() => $paybylinkService->validateWebhook($webhookRequest, $payment))->toThrow(Ifthenpay\PaymentGateway\Exception\WebhookValidationException::class, 'OrderId does not match');
    });

    it('throws WebhookValidationException if amount does not match', function () {
        $ifthenpayGateway = new IfthenpayGateway($this->configArray);
        $paybylinkService = $ifthenpayGateway->paybylink();

        $webhookRequest = new Ifthenpay\PaymentGateway\RequestObj\WebhookRequest(...[
            'orderId'        => '01',
            'amount'         => '0.20',
            'transactionId'  => 'tid000000109',
            'antiPhishingKey' => $this->configArray['antiPhishingKey']
        ]);

        $payment = new PayByLink('0.50', '01', '1111111111', 'https://example-paybylink.com/payment/123456', Status::PENDING);

        expect(fn() => $paybylinkService->validateWebhook($webhookRequest, $payment))->toThrow(Ifthenpay\PaymentGateway\Exception\WebhookValidationException::class, 'Amount does not match');
    });
});


describe('[FEATURE] paybylink registerWebhook', function () {
    it('registers webhook successfully with valid url', function () {

        $numberOfMethodAccountsToRegister = count($this->configArray['payByLink']['methodAccounts']);
        $mockClient = mockClientRegisterWebhook($numberOfMethodAccountsToRegister);

        $ifthenpayGateway = new IfthenpayGateway($this->configArray, $mockClient);

        expect($ifthenpayGateway->paybylink()->registerWebhook('https://example.com/webhook'))->not->toThrow(Ifthenpay\PaymentGateway\Exception\EndpointResponseException::class);
    });
    it('throws exception if url length exceeds 300 characters', function () {

        $ifthenpayGateway = new IfthenpayGateway($this->configArray);
        $result = $ifthenpayGateway->paybylink()->registerWebhook('https://example.com/' . str_repeat('a', 290));
    })->throws(InvalidArgumentException::class, 'length must be equal or less than 300 characters.');
    it('throws exception if url not valid url string', function () {

        $ifthenpayGateway = new IfthenpayGateway($this->configArray);
        $result = $ifthenpayGateway->paybylink()->registerWebhook('testurl');
    })->throws(InvalidArgumentException::class, 'must be a valid URL.');
});


describe('[FEATURE] paybylink isTransactionPaid', function () {

    it('returns payment status as MethodCode enum "Multibanco"', function () {

        $responsePayload = [
            'TransactionId' => '1111111111111111111',
            'PaymentMethod' => 'MULTIBANCO'
        ];

        $mockStream = Mockery::mock(StreamInterface::class);
        $mockStream->shouldReceive('__toString')
            ->andReturn(json_encode($responsePayload));

        $response = Mockery::mock(ResponseInterface::class);
        $response->shouldReceive('getBody')->andReturn($mockStream);

        $mockClient = Mockery::mock(ClientInterface::class);
        $mockClient->shouldReceive('sendRequest')
            ->once()
            ->with(mockedGetRequest($this->config->endpoint('paybylink_status') . '?' . http_build_query(['transactionId' => '1111111111111111111'])))
            ->andReturn($response);

        // transaction ID will only be present after recieving webhook
        $webhookRequest = new Ifthenpay\PaymentGateway\RequestObj\WebhookRequest(...[
            'orderId'        => '01',
            'amount'         => '0.50',
            'transactionId'  => '1111111111111111111',
            'antiPhishingKey' => $this->configArray['antiPhishingKey']
        ]);

        $ifthenpayGateway = new IfthenpayGateway($this->configArray, $mockClient);

        $result = $ifthenpayGateway->paybylink()->isTransactionPaid($webhookRequest->transactionId);
        expect($result)->toBe(MethodCode::MULTIBANCO_STATIC);
    });

    it('returns payment status as false', function () {

        $responsePayload = [];

        $mockStream = Mockery::mock(StreamInterface::class);
        $mockStream->shouldReceive('__toString')
            ->andReturn(json_encode($responsePayload));

        $response = Mockery::mock(ResponseInterface::class);
        $response->shouldReceive('getBody')->andReturn($mockStream);

        $mockClient = Mockery::mock(ClientInterface::class);
        $mockClient->shouldReceive('sendRequest')
            ->once()
            ->with(mockedGetRequest($this->config->endpoint('paybylink_status') . '?' . http_build_query(['transactionId' => '1111111111111111111'])))
            ->andReturn($response);

        // transaction ID will only be present after recieving webhook
        $webhookRequest = new Ifthenpay\PaymentGateway\RequestObj\WebhookRequest(...[
            'orderId'        => '01',
            'amount'         => '0.50',
            'transactionId'  => '1111111111111111111',
            'antiPhishingKey' => $this->configArray['antiPhishingKey']
        ]);

        $ifthenpayGateway = new IfthenpayGateway($this->configArray, $mockClient);

        $result = $ifthenpayGateway->paybylink()->isTransactionPaid($webhookRequest->transactionId);
        expect($result)->toBeFalse();
    });
});



describe('[FEATURE] paybylink isExpired', function () {

    it('returns payment expired status as true', function () {

        $pastDate = DateTimeImmutable::createFromMutable(new \DateTime('now'));

        $ifthenpayGateway = new IfthenpayGateway($this->configArray);

        $payment = new PayByLink('0.50', '01', '1111111111', 'https://example-paybylink.com/payment/123456', Status::PENDING, $pastDate);
        $result = $ifthenpayGateway->paybylink()->isExpired($payment);
        expect($result)->toBeTrue();
    });
    it('returns payment expired status as false', function () {

        $futureDate = DateTimeImmutable::createFromMutable(new \DateTime('now + 1minute'));

        $ifthenpayGateway = new IfthenpayGateway($this->configArray);
        $payment = new PayByLink('0.50', '01', '1111111111', 'https://example-paybylink.com/payment/123456', Status::PENDING, $futureDate);
        $result = $ifthenpayGateway->paybylink()->isExpired($payment);
        expect($result)->toBeFalse();
    });
    it('returns payment expired status as false when expire not set', function () {

        $ifthenpayGateway = new IfthenpayGateway($this->configArray);
        $payment = new PayByLink('0.50', '01', '1111111111', 'https://example-paybylink.com/payment/123456', Status::PENDING);
        $result = $ifthenpayGateway->paybylink()->isExpired($payment);
        expect($result)->toBeFalse();
    });
});
