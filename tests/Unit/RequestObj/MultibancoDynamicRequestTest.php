<?php

use Ifthenpay\PaymentGateway\RequestObj\MultibancoDynamicInitRequest;

beforeEach(function () {

    $this->dataArray = [
        'multibancoKey' => 'ITP-000000',
        'orderId' => '01',
        'amount' => '15.00',
        'description' => 'Test Description',
        'daysToExpire' => 3,
    ];
});


describe('[UNIT] RequestObj/MultibancoDynamicInitRequest', function () {
    it('creates a request object successfully', function () {

        $requestObj = new MultibancoDynamicInitRequest(...$this->dataArray);

        expect($requestObj)->toBeInstanceOf(MultibancoDynamicInitRequest::class);
        expect($requestObj->toPayload())->not->toBeNull();
    });

    // multibancoKey
    it('throws an exception when multibancoKey empty string', function () {
        $this->dataArray['multibancoKey'] = '';
        new MultibancoDynamicInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "is required.");

    it('throws an exception if multibancoKey invalid format A1A-000000', function () {

        $this->dataArray['multibancoKey'] = 'A1A-000000';
        new MultibancoDynamicInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "must be a valid key");

    // orderId
    it('throws an exception when orderId empty string', function () {
        $this->dataArray['orderId'] = '';
        new MultibancoDynamicInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "is required.");

    it('throws an exception when fields exceed max length', function () {

        $this->dataArray['orderId'] = str_repeat('1', 26);
        new MultibancoDynamicInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "length must be equal or less than 25 characters.");

    // amount
    it('throws an exception when amount empty string', function () {
        $this->dataArray['amount'] = '';
        new MultibancoDynamicInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "is required.");

    it('throws an exception when amount invalid format', function () {

        $this->dataArray['amount'] = '10,00';
        new MultibancoDynamicInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "must be a positive decimal number with a '.' as the separator");

    it('throws an exception when amount is negative', function () {

        $this->dataArray['amount'] = '-10.00';
        new MultibancoDynamicInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "must be a positive decimal number with a '.' as the separator");

    it('throws an exception when amount exceed max length', function () {

        $this->dataArray['amount'] = str_repeat('1', 8) . '.00';
        new MultibancoDynamicInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "length must be equal or less than 10 characters.");

    // description
    it('throws an exception when description exceed max length', function () {
        $this->dataArray['description'] = str_repeat('a', 256);
        new MultibancoDynamicInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "length must be equal or less than 255 characters.");

    // daysToExpire
    it('throws an exception when daysToExpire has invalid format', function () {
        $this->dataArray['daysToExpire'] = 33;
        new MultibancoDynamicInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "must be an integer matching 1 to 32 or 45, 60, 90, 120.");


    // nullable fields
    it('creates object with nullable properties', function () {
        $this->dataArray['description'] = null;
        $this->dataArray['daysToExpire'] = null;
        $requestObj = new MultibancoDynamicInitRequest(...$this->dataArray);
        expect($requestObj)->toBeInstanceOf(MultibancoDynamicInitRequest::class);
        expect($requestObj->description)->toEqual(null);
        expect($requestObj->daysToExpire)->toEqual(null);
    });

    it('returns the array version of the object', function () {

        $this->dataArray['daysToExpire'] = 60;
        $requestObj = new MultibancoDynamicInitRequest(...$this->dataArray);
        expect($requestObj->toPayload())->toEqual([
            "mbKey" => $this->dataArray['multibancoKey'],
            "orderId" => $this->dataArray['orderId'],
            "amount" => $this->dataArray['amount'],
            "description" => $this->dataArray['description'],
            "expiryDays" => $this->dataArray['daysToExpire'],
        ]);
    });
});
