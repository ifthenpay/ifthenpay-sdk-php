<?php

use Ifthenpay\PaymentGateway\RequestObj\MbwayInitRequest;

beforeEach(function () {

    $this->dataArray = [
        'mbwayKey' => 'ITP-000000',
        'orderId' => '01',
        'amount' => '10.00',
        'mobileNumber' => '912345678',
        'description' => 'Test Description',
        'email' => 'test@email.com',
    ];
});


describe('[UNIT] RequestObj/MbwayInitRequest', function () {
    it('creates a request object successfully', function () {

        $requestObj = new MbwayInitRequest(...$this->dataArray);

        expect($requestObj)->toBeInstanceOf(MbwayInitRequest::class);
        expect($requestObj->toPayload())->not->toBeNull();
    });

    // mbwayKey
    it('throws an exception when mbwayKey empty string', function () {

        $this->dataArray['mbwayKey'] = '';
        new MbwayInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "'mbwayKey' is required.");


    it('throws an exception if mbwayKey invalid format A1A-000000', function () {

        $this->dataArray['mbwayKey'] = 'A1A-000000';
        new MbwayInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "must be a valid key");

    // orderId
    it('throws an exception when orderId empty string', function () {

        $this->dataArray['orderId'] = '';
        new MbwayInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "'orderId' is required.");

    it('throws an exception when orderId exceed max length', function () {

        $this->dataArray['orderId'] = '11112222233334444455556660';
        new MbwayInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "'orderId' length must be equal or less than 25 characters.");


    // amount

    it('throws an exception when amount empty string', function () {

        $this->dataArray['amount'] = '';
        new MbwayInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "'amount' is required.");

    it('throws an exception when amount have invalid format', function () {

        $this->dataArray['amount'] = 'invalid_amount';
        new MbwayInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "must be a positive decimal number with a '.' as the separator");


    // mobileNumber
    it('throws an exception when mobileNumber empty string', function () {

        $this->dataArray['mobileNumber'] = '';
        new MbwayInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "'mobileNumber' is required.");

    it('throws an exception when mobileNumber has invalid format', function () {

        $this->dataArray['mobileNumber'] = '123ABC';
        new MbwayInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "must be a valid mobile number in the format");


    // description
    it('throws an exception when description exceed max length', function () {

        $this->dataArray['description'] = str_repeat('a', 101);
        new MbwayInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "'description' length must be equal or less than 100 characters.");

    // email
    it('throws an exception when email not valid', function () {
        $this->dataArray['email'] = 'invalid_email';
        new MbwayInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "must be a valid email address.");

    it('throws an exception when email exceed max length', function () {
        $this->dataArray['email'] = str_repeat('a', 64) . '@' . str_repeat('a', 27) . 'email.com';
        new MbwayInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "'email' length must be equal or less than 100 characters.");

    // nullable fields
    it('creates object with nullable properties', function () {
        $this->dataArray['description'] = null;
        $this->dataArray['email'] = null;
        $requestObj = new MbwayInitRequest(...$this->dataArray);
        expect($requestObj)->toBeInstanceOf(MbwayInitRequest::class);
        expect($requestObj->description)->toEqual(null);
        expect($requestObj->email)->toEqual(null);
    });


    it('returns the array version of the object', function () {
        $requestObj = new MbwayInitRequest(...$this->dataArray);
        expect($requestObj->toPayload())->toEqual([
            "mbWayKey"     => $this->dataArray['mbwayKey'],
            "orderId"      => $this->dataArray['orderId'],
            "amount"       => $this->dataArray['amount'],
            "mobileNumber" => $this->dataArray['mobileNumber'],
            "email"        => $this->dataArray['email'],
            "description"  => $this->dataArray['description']
        ]);
    });
});
