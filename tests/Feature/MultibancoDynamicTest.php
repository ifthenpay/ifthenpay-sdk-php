<?php

use Ifthenpay\PaymentGateway\Config;
use Ifthenpay\PaymentGateway\Enums\Status;
use Ifthenpay\PaymentGateway\Exception\WebhookValidationException;
use Ifthenpay\PaymentGateway\IfthenpayGateway;
use Ifthenpay\PaymentGateway\Model\MultibancoDynamic;
use Ifthenpay\PaymentGateway\Utils\DateTools;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

beforeEach(function () {

    $this->configArray = [
        'multibancoDynamic' => [
            'key'    => 'ITP-000000',
        ],
        'backofficeKey'   => '1111-1111-1111-1111',
        'antiPhishingKey' => '1234123412341234',
    ];
    $this->config = Config::fromArray($this->configArray);
});


describe('[FEATURE] MultibancoDynamic initPayment', function () {

    it('initializes payment successfully without deadline', function () {

        $requestPayload = [
            'mbKey'      => 'ITP-000000',
            'orderId'    => '01',
            'amount'     => '0.50',
            'description'        => null,
            'expiryDays' => null
        ];

        $responsePayload = [
            'Amount'     => '0.50',
            'Entity'     => '12345',
            'ExpiryDate' => '',
            'Message'    => 'Success',
            'OrderId'    => '01',
            'Reference'  => '000286424',
            'RequestId'  => 'OVoC0v7IxLTPspHRmBcA',
            'Status'     => '0'
        ];

        $mockStream = Mockery::mock(StreamInterface::class);
        $mockStream->shouldReceive('__toString')
            ->andReturn(json_encode($responsePayload));

        $response = Mockery::mock(ResponseInterface::class);
        $response->shouldReceive('getBody')->andReturn($mockStream);

        $mockClient = Mockery::mock(ClientInterface::class);

        $mockClient->shouldReceive('sendRequest')
            ->once()
            ->with(mockedPostRequest($this->config->endpoint('multibanco_init'), $requestPayload))
            ->andReturn($response);

        $ifthenpayGateway = new IfthenpayGateway($this->configArray, $mockClient);
        $multibancoPayment = $ifthenpayGateway->multibancoDynamic()->initPayment('01', '0.50');

        expect($multibancoPayment)->toBeInstanceOf(MultibancoDynamic::class)
            ->and($multibancoPayment->getAmount())->toBe('0.50')
            ->and($multibancoPayment->getOrderId())->toBe('01')
            ->and($multibancoPayment->getEntity())->toBe($responsePayload['Entity'])
            ->and($multibancoPayment->getReference())->toBe($responsePayload['Reference'])
            ->and($multibancoPayment->getTransactionId())->toBe($responsePayload['RequestId'])
            ->and($multibancoPayment->getExpireDate())->toBeNull()
            ->and($multibancoPayment->getStatus())->toBe(Status::PENDING);
    });


    it('initializes payment successfully with deadline', function () {

        $requestPayload = [
            'mbKey'      => 'ITP-000000',
            'orderId'    => '01',
            'amount'     => '0.50',
            'description'        => null,
            'expiryDays' => 5
        ];


        $responsePayload = [
            'Amount'     => '0.50',
            'Entity'     => '12345',
            'ExpiryDate' => DateTools::getFutureDate(5)->format('d-m-Y'),
            'Message'    => 'Success',
            'OrderId'    => '01',
            'Reference'  => '000286424',
            'RequestId'  => 'OVoC0v7IxLTPspHRmBcA',
            'Status'     => '0'
        ];

        $mockStream = Mockery::mock(StreamInterface::class);
        $mockStream->shouldReceive('__toString')
            ->andReturn(json_encode($responsePayload));

        $response = Mockery::mock(ResponseInterface::class);
        $response->shouldReceive('getBody')->andReturn($mockStream);

        $mockClient = Mockery::mock(ClientInterface::class);
        $mockClient->shouldReceive('sendRequest')
            ->once()
            ->with(mockedPostRequest($this->config->endpoint('multibanco_init'), $requestPayload))
            ->andReturn($response);

        $ifthenpayGateway = new IfthenpayGateway($this->configArray, $mockClient);
        $multibancoPayment = $ifthenpayGateway->multibancoDynamic()->initPayment('01', '0.50', null, 5);


        expect($multibancoPayment)->toBeInstanceOf(MultibancoDynamic::class)
            ->and($multibancoPayment->getAmount())->toBe('0.50')
            ->and($multibancoPayment->getOrderId())->toBe('01')
            ->and($multibancoPayment->getEntity())->toBe($responsePayload['Entity'])
            ->and($multibancoPayment->getReference())->toBe($responsePayload['Reference'])
            ->and($multibancoPayment->getTransactionId())->toBe($responsePayload['RequestId'])
            ->and($multibancoPayment->getExpireDate())->toBeInstanceof(DateTimeImmutable::class)
            ->and($multibancoPayment->getExpireDate()->format('Y-m-d H:i'))->toBe(DateTools::getFutureDate(5, 23, 59, true)->format('Y-m-d H:i'))
            ->and($multibancoPayment->getStatus())->toBe(Status::PENDING);
    });

    it('throws exception if invalid orderId', function () {

        $ifthenpayGateway = new IfthenpayGateway($this->configArray);
        $result = $ifthenpayGateway->multibancoDynamic()->initPayment('', '0.50');
    })->throws(InvalidArgumentException::class, "'orderId' is required.");

    it('throws exception if invalid amount', function () {

        $ifthenpayGateway = new IfthenpayGateway($this->configArray);
        $result = $ifthenpayGateway->multibancoDynamic()->initPayment('01', '0.00');
    })->throws(InvalidArgumentException::class, "must be a positive decimal number with a '.' as the separator");

    it('throws exception if invalid daysToExpire', function () {

        $ifthenpayGateway = new IfthenpayGateway($this->configArray);
        $result = $ifthenpayGateway->multibancoDynamic()->initPayment('01', '0.50', null, -1);
    })->throws(InvalidArgumentException::class, "must be an integer matching 1 to 32 or 45, 60, 90, 120.");
});



describe('[FEATURE] multibancoDynamic registerWebhook', function () {
    it('registers webhook successfully with valid url', function () {

        $mockClient = mockClientRegisterWebhook();

        $ifthenpayGateway = new IfthenpayGateway($this->configArray, $mockClient);
        expect($ifthenpayGateway->multibancoDynamic()->registerWebhook('https://example.com/webhook'))->not->toThrow(Ifthenpay\PaymentGateway\Exception\EndpointResponseException::class);
    });

    it('throws exception if url length greater than 300', function () {

        $ifthenpayGateway = new IfthenpayGateway($this->configArray);
        $result = $ifthenpayGateway->multibancoDynamic()->registerWebhook('https://example.com/webhook11111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111webhook11111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111');
    })->throws(InvalidArgumentException::class, 'length must be equal or less than 300 characters.');

    it('throws exception if url not valid url string', function () {

        $ifthenpayGateway = new IfthenpayGateway($this->configArray);
        $result = $ifthenpayGateway->multibancoDynamic()->registerWebhook('testurl');
    })->throws(InvalidArgumentException::class, 'must be a valid URL.');
});



describe('[FEATURE] multibancoDynamic isPaid', function () {

    it('returns payment paid status as true', function () {

        $mockClient = mockClientIsPaidHttp(true);

        $ifthenpayGateway = new IfthenpayGateway($this->configArray, $mockClient);

        $multibancoDynamicPayment = new multibancoDynamic('0.50', '01', '12345', '000000109', 'tid000000109', Status::PENDING, null, DateTools::getTimeStamp());
        $result = $ifthenpayGateway->multibancoDynamic()->isPaid($multibancoDynamicPayment);

        expect($result)->toBeTrue();
    });


    it('returns payment paid status as false', function () {

        $mockClient = mockClientIsPaidHttp(false);

        $ifthenpayGateway = new IfthenpayGateway($this->configArray, $mockClient);
        $multibancoDynamicPayment = new multibancoDynamic('0.50', '01', '12345', '000000109', 'tid000000109', Status::PENDING, null, DateTools::getTimeStamp());

        $result = $ifthenpayGateway->multibancoDynamic()->isPaid($multibancoDynamicPayment);

        expect($result)->toBeFalse();
    });
});


// validateWebhook
describe('[FEATURE] multibancoDynamic validateWebhook', function () {
    it('returns void (valid webhook)', function () {

        $webhookRequest = new Ifthenpay\PaymentGateway\RequestObj\WebhookRequest(...[
            'orderId'        => '01',
            'amount'         => '0.50',
            'reference'      => '000000109',
            'transactionId'  => 'tid000000109',
            'antiPhishingKey' => $this->configArray['antiPhishingKey']
        ]);

        $ifthenpayGateway = new IfthenpayGateway($this->configArray);
        $multibancoDynamicPayment = new multibancoDynamic('0.50', '01', '12345', '000000109', 'tid000000109', Status::PENDING, null, DateTools::getTimeStamp());

        expect(fn() => $ifthenpayGateway->multibancoDynamic()->validateWebhook($webhookRequest, $multibancoDynamicPayment))
            ->not->toThrow(WebhookValidationException::class);
    });

    it('throws exception if missing webhook parameter (reference)', function () {

        $webhookRequest = new Ifthenpay\PaymentGateway\RequestObj\WebhookRequest(...[
            'orderId'        => '01',
            'amount'         => '0.50',
            'antiPhishingKey' => $this->configArray['antiPhishingKey']
        ]);

        $ifthenpayGateway = new IfthenpayGateway($this->configArray);
        $multibancoDynamicPayment = new multibancoDynamic('0.50', '01', '12345', '000000109', 'tid000000109', Status::PENDING, null, DateTools::getTimeStamp());

        expect(fn() => $ifthenpayGateway->multibancoDynamic()->validateWebhook($webhookRequest, $multibancoDynamicPayment))
            ->toThrow(WebhookValidationException::class, 'Missing webhook parameter');
    });

    it('throws exception if different webhook parameter', function () {

        $webhookRequest = new Ifthenpay\PaymentGateway\RequestObj\WebhookRequest(...[
            'orderId'        => '01',
            'amount'         => '0.50',
            'reference'      => '000000109',
            'transactionId'  => 'tid000000110',
            'antiPhishingKey' => $this->configArray['antiPhishingKey']
        ]);

        $ifthenpayGateway = new IfthenpayGateway($this->configArray);
        $multibancoDynamicPayment = new multibancoDynamic('0.50', '01', '12345', '000000109', 'tid000000109', Status::PENDING, null, DateTools::getTimeStamp());

        expect(fn() => $ifthenpayGateway->multibancoDynamic()->validateWebhook($webhookRequest, $multibancoDynamicPayment))
            ->toThrow(WebhookValidationException::class, 'does not match');
    });
});



describe('[FEATURE] multibancoDynamic isExpired', function () {

    it('returns payment expired status as true', function () {

        $pastDate = DateTimeImmutable::createFromMutable(new \DateTime('now'));

        $ifthenpayGateway = new IfthenpayGateway($this->configArray);

        $payment = new multibancoDynamic('0.50', '01', '12345', '000000109', 'tid000000109', Status::PENDING, $pastDate);
        $result = $ifthenpayGateway->multibancoDynamic()->isExpired($payment);
        expect($result)->toBeTrue();
    });
    it('returns payment expired status as false', function () {

        $futureDate = DateTimeImmutable::createFromMutable(new \DateTime('now + 1minute'));

        $ifthenpayGateway = new IfthenpayGateway($this->configArray);
        $payment = new multibancoDynamic('0.50', '01', '12345', '000000109', 'tid000000109', Status::PENDING, $futureDate);
        $result = $ifthenpayGateway->multibancoDynamic()->isExpired($payment);
        expect($result)->toBeFalse();
    });
    it('returns payment expired status as false when expire not set', function () {

        $ifthenpayGateway = new IfthenpayGateway($this->configArray);
        $payment = new multibancoDynamic('0.50', '01', '12345', '000000109', 'tid000000109', Status::PENDING);
        $result = $ifthenpayGateway->multibancoDynamic()->isExpired($payment);
        expect($result)->toBeFalse();
    });
});
