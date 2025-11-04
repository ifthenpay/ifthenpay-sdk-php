<?php

use Ifthenpay\PaymentGateway\RequestObj\WebhookRequest;

beforeEach(function () {

    $this->dataArray = [
        'amount'          => '10.00',
        'orderId'         => '01',
        'antiPhishingKey' => 'abcde12345',
        'transactionId'   => '1234567890',
    ];
});


describe('[UNIT] RequestObj/WebhookRequest', function () {

    it('creates a request object successfully', function () {

        $requestObj = new WebhookRequest(...$this->dataArray);

        expect($requestObj)->toBeInstanceOf(WebhookRequest::class);
        expect($requestObj->toArray())->not->toBeNull();
    });

    it('creates object with reference null', function () {

        $requestObj = new WebhookRequest(...$this->dataArray);
        expect($requestObj->reference)->toEqual(null);
    });

    it('creates object with transactionId null', function () {

        $this->dataArray['transactionId'] = null;
        $requestObj = new WebhookRequest(...$this->dataArray);
        expect($requestObj->transactionId)->toEqual(null);
    });

    it('returns the array version of the object', function () {
        $requestObj = new WebhookRequest(...$this->dataArray);
        expect($requestObj->toArray())->toEqual([
            'val' => '10.00',
            'oid' => '01',
            'tid' => '1234567890',
            'ref' => null,
            'apk' => 'abcde12345',
        ]);
    });
});
