<?php

use Ifthenpay\PaymentGateway\Enums\Status;
use Ifthenpay\PaymentGateway\Exception\WebhookValidationException;
use Ifthenpay\PaymentGateway\IfthenpayGateway;
use Ifthenpay\PaymentGateway\Model\MultibancoOffline;
use Ifthenpay\PaymentGateway\Utils\DateTools;

beforeEach(function () {

    $this->configArray = [
        'multibancoOffline' => [
            'entity'    => '11111',
            'subEntity' => '111',
        ],
        'backofficeKey'   => '1111-1111-1111-1111',
        'antiPhishingKey' => '1234123412341234',
    ];
});


describe('[FEATURE] MultibancoOffline initPayment', function () {
    it('initializes payment successfully without deadline', function () {

        $ifthenpayGateway = new IfthenpayGateway($this->configArray);
        $result = $ifthenpayGateway->multibancoOffline()->initPayment('01', '0.50');

        expect($result)->toBeInstanceOf(MultibancoOffline::class)
            ->and($result->getAmount())->toBe('0.50')
            ->and($result->getOrderId())->toBe('01')
            ->and($result->getEntity())->toBe($this->configArray['multibancoOffline']['entity'])
            ->and($result->getReference())->toBe('111000109')
            ->and($result->getExpireDate())->toBeNull()
            ->and($result->getStatus())->toBe(Status::PENDING);
    });

    it('initializes payment successfully with deadline', function () {

        $this->configArray['multibancoOffline']['daysToExpire'] = 5;

        $ifthenpayGateway = new IfthenpayGateway($this->configArray);
        $result = $ifthenpayGateway->multibancoOffline()->initPayment('01', '0.50');

        expect($result)->toBeInstanceOf(MultibancoOffline::class)
            ->and($result->getAmount())->toBe('0.50')
            ->and($result->getOrderId())->toBe('01')
            ->and($result->getEntity())->toBe($this->configArray['multibancoOffline']['entity'])
            ->and($result->getReference())->toBe('111000109')
            ->and($result->getExpireDate())->toBeInstanceof(DateTimeImmutable::class)
            ->and($result->getExpireDate()->format('Y-m-d H:i'))->toBe(DateTools::getFutureDate(5, 23, 59, true)->format('Y-m-d H:i'))
            ->and($result->getStatus())->toBe(Status::PENDING);
    });

    it('throws exception if invalid orderId', function () {

        $ifthenpayGateway = new IfthenpayGateway($this->configArray);
        $result = $ifthenpayGateway->multibancoOffline()->initPayment('', '0.50');
    })->throws(InvalidArgumentException::class, "'orderId' is required.");

    it('throws exception if invalid amount', function () {

        $ifthenpayGateway = new IfthenpayGateway($this->configArray);
        $result = $ifthenpayGateway->multibancoOffline()->initPayment('01', '0.00');
    })->throws(InvalidArgumentException::class, "must be a positive decimal number with a '.' as the separator");
});



describe('[FEATURE] MultibancoOffline registerWebhook', function () {
    it('registers webhook successfully with valid url', function () {

        $mockClient = mockClientRegisterWebhook();
        $ifthenpayGateway = new IfthenpayGateway($this->configArray, $mockClient);

        expect($ifthenpayGateway->multibancoOffline()->registerWebhook('https://example.com/webhook'))->not->toThrow(Ifthenpay\PaymentGateway\Exception\EndpointResponseException::class);
    });

    it('throws exception if url length greater than 300', function () {

        $ifthenpayGateway = new IfthenpayGateway($this->configArray);
        $result = $ifthenpayGateway->multibancoOffline()->registerWebhook('https://example.com/webhook11111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111webhook11111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111');
    })->throws(InvalidArgumentException::class, 'length must be equal or less than 300 characters.');

    it('throws exception if url not valid url string', function () {

        $ifthenpayGateway = new IfthenpayGateway($this->configArray);
        $result = $ifthenpayGateway->multibancoOffline()->registerWebhook('testurl');
    })->throws(InvalidArgumentException::class, 'must be a valid URL.');
});



describe('[FEATURE] MultibancoOffline isPaid', function () {

    it('returns payment paid status as true', function () {

        $mockClient = mockClientIsPaidHttp(true);

        $ifthenpayGateway = new IfthenpayGateway($this->configArray, $mockClient);

        $multibancoOfflinePayment = new MultibancoOffline('0.50', '01', '12345', '111000109', Status::PENDING, null, DateTools::getTimeStamp());
        $result = $ifthenpayGateway->multibancoOffline()->isPaid($multibancoOfflinePayment);

        expect($result)->toBeTrue();
    });


    it('returns payment paid status as false', function () {

        $mockClient = mockClientIsPaidHttp(false);

        $ifthenpayGateway = new IfthenpayGateway($this->configArray, $mockClient);
        $multibancoOfflinePayment = new MultibancoOffline('0.50', '01', '12345', '111000109', Status::PENDING, null, DateTools::getTimeStamp());

        $result = $ifthenpayGateway->multibancoOffline()->isPaid($multibancoOfflinePayment);

        expect($result)->toBeFalse();
    });
});


// validateWebhook
describe('[FEATURE] MultibancoOffline validateWebhook', function () {
    it('returns void (valid webhook)', function () {

        $webhookRequest = new Ifthenpay\PaymentGateway\RequestObj\WebhookRequest(...[
            'orderId'        => '01',
            'amount'         => '0.50',
            'reference'      => '111000109',
            'antiPhishingKey' => $this->configArray['antiPhishingKey']
        ]);

        $ifthenpayGateway = new IfthenpayGateway($this->configArray);
        $multibancoOfflinePayment = new MultibancoOffline('0.50', '01', '12345', '111000109', Status::PENDING, null, DateTools::getTimeStamp());

        expect(fn() => $ifthenpayGateway->multibancoOffline()->validateWebhook($webhookRequest, $multibancoOfflinePayment))
            ->not->toThrow(WebhookValidationException::class);
    });

    it('throws exception if missing webhook parameter (reference)', function () {

        $webhookRequest = new Ifthenpay\PaymentGateway\RequestObj\WebhookRequest(...[
            'orderId'        => '01',
            'amount'         => '0.50',
            'antiPhishingKey' => $this->configArray['antiPhishingKey']
        ]);

        $ifthenpayGateway = new IfthenpayGateway($this->configArray);
        $multibancoOfflinePayment = new MultibancoOffline('0.50', '01', '12345', '111000109', Status::PENDING, null, DateTools::getTimeStamp());

        expect(fn() => $ifthenpayGateway->multibancoOffline()->validateWebhook($webhookRequest, $multibancoOfflinePayment))
            ->toThrow(WebhookValidationException::class, 'Missing webhook parameter');
    });

    it('throws exception if different webhook parameter', function () {

        $webhookRequest = new Ifthenpay\PaymentGateway\RequestObj\WebhookRequest(...[
            'orderId'        => '01',
            'amount'         => '0.50',
            'reference'      => '000000110',
            'antiPhishingKey' => $this->configArray['antiPhishingKey']
        ]);

        $ifthenpayGateway = new IfthenpayGateway($this->configArray);
        $multibancoOfflinePayment = new MultibancoOffline('0.50', '01', '12345', '111000109', Status::PENDING, null, DateTools::getTimeStamp());

        expect(fn() => $ifthenpayGateway->multibancoOffline()->validateWebhook($webhookRequest, $multibancoOfflinePayment))
            ->toThrow(WebhookValidationException::class, 'does not match');
    });
});



describe('[FEATURE] multibancoOffline isExpired', function () {

    it('returns payment expired status as true', function () {

        $pastDate = DateTimeImmutable::createFromMutable(new \DateTime('now'));

        $ifthenpayGateway = new IfthenpayGateway($this->configArray);

        $payment = new multibancoOffline('0.50', '01', '12345', '111000109', Status::PENDING, $pastDate);
        $result = $ifthenpayGateway->multibancoOffline()->isExpired($payment);
        expect($result)->toBeTrue();
    });
    it('returns payment expired status as false', function () {

        $futureDate = DateTimeImmutable::createFromMutable(new \DateTime('now + 1minute'));

        $ifthenpayGateway = new IfthenpayGateway($this->configArray);
        $payment = new multibancoOffline('0.50', '01', '12345', '111000109', Status::PENDING, $futureDate);
        $result = $ifthenpayGateway->multibancoOffline()->isExpired($payment);
        expect($result)->toBeFalse();
    });
    it('returns payment expired status as false when expire not set', function () {

        $ifthenpayGateway = new IfthenpayGateway($this->configArray);
        $payment = new multibancoOffline('0.50', '01', '12345', '111000109', Status::PENDING);
        $result = $ifthenpayGateway->multibancoOffline()->isExpired($payment);
        expect($result)->toBeFalse();
    });
});
