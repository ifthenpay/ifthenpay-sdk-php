<?php

use Ifthenpay\PaymentGateway\RequestObj\RegisterWebhookRequest;

beforeEach(function () {

    $this->dataArray = [
        'backofficeKey'   => '1111-1111-1111-1111',
        'entity'          => 'MBWAY',
        'subEntity'       => 'ITP-000000',
        'antiPhishingKey' => 'abcde12345',
        'callbackUrl'     => 'https://your-callback-url.com',
    ];
});


describe('[UNIT] RequestObj/RegisterWebhookRequest', function () {

    it('creates a request object successfully', function () {

        $requestObj = new RegisterWebhookRequest(...$this->dataArray);

        expect($requestObj)->toBeInstanceOf(RegisterWebhookRequest::class);
        expect($requestObj->toPayload())->not->toBeNull();
    });

    // backofficeKey
    it('throws an exception when backofficeKey empty string', function () {
        $this->dataArray['backofficeKey'] = '';
        new RegisterWebhookRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "'backofficeKey' is required.");

    it('throws an exception if backofficeKey invalid format 1111-1111-1111-111f', function () {

        $this->dataArray['backofficeKey'] = '1111-1111-1111-111f';
        new RegisterWebhookRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "must be a valid backoffice key");

    it('throws an exception if backofficeKey invalid format 1111-1111-1111', function () {

        $this->dataArray['backofficeKey'] = '1111-1111-1111';
        new RegisterWebhookRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "must be a valid backoffice key");

    it('throws an exception if backofficeKey invalid format ITP-000000', function () {

        $this->dataArray['backofficeKey'] = 'ITP-000000';
        new RegisterWebhookRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "must be a valid backoffice key");

    // entity
    it('throws an exception when entity empty string', function () {
        $this->dataArray['entity'] = '';
        new RegisterWebhookRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "'entity' is required.");

    it('throws an exception if entity not in enum MethodCode', function () {

        $this->dataArray['entity'] = 'MBWAYX';
        new RegisterWebhookRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "'entity' must be one of the following values:");

    // subEntity
    it('throws an exception when subEntity empty string', function () {
        $this->dataArray['subEntity'] = '';
        new RegisterWebhookRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "'subEntity' is required.");

    it('throws an exception if subEntity invalid format A1A-000000', function () {

        $this->dataArray['subEntity'] = 'A1A-000000';
        new RegisterWebhookRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "must be a valid key");

    it('throws an exception if subEntity invalid format AITP-000000', function () {

        $this->dataArray['subEntity'] = 'AITP-000000';
        new RegisterWebhookRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "must be a valid key");


    // antiPhishingKey
    it('throws an exception when antiPhishingKey empty string', function () {
        $this->dataArray['antiPhishingKey'] = '';
        new RegisterWebhookRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "'antiPhishingKey' is required.");

    it('throws an exception when antiPhishingKey less than min length', function () {

        $this->dataArray['antiPhishingKey'] = str_repeat('a', 9);
        new RegisterWebhookRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "length must be equal or greater than 10 characters.");


    it('throws an exception when antiPhishingKey exceed max length', function () {

        $this->dataArray['antiPhishingKey'] = str_repeat('a', 51);
        new RegisterWebhookRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "length must be equal or less than 50 characters.");

    // callbackUrl
    it('throws an exception when callbackUrl empty string', function () {
        $this->dataArray['callbackUrl'] = '';
        new RegisterWebhookRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "'callbackUrl' is required.");

    it('throws an exception when callbackUrl not valid url', function () {

        $this->dataArray['callbackUrl'] = 'somesite.com';
        new RegisterWebhookRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "must be a valid URL.");


    it('throws an exception when callbackUrl exceed max length', function () {

        $this->dataArray['callbackUrl'] = 'https://yourdomain.com/' . str_repeat('a', 278);
        new RegisterWebhookRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "length must be equal or less than 300 characters.");


    it('returns the array version of the object', function () {
        $requestObj = new RegisterWebhookRequest(...$this->dataArray);
        expect($requestObj->toPayload())->toEqual([
            'chave'             => $this->dataArray['backofficeKey'],
            'entidade'          => $this->dataArray['entity'],
            'subentidade'       => $this->dataArray['subEntity'],
            'antiPhishingKey' => $this->dataArray['antiPhishingKey'],
            'urlCb'             => $this->dataArray['callbackUrl']
        ]);
    });
});
