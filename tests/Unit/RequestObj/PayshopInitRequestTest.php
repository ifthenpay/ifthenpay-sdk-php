<?php

use Ifthenpay\PaymentGateway\RequestObj\PayshopInitRequest;
use Ifthenpay\PaymentGateway\Utils\DateTools;

beforeEach(function () {

    $this->dataArray = [
        'payshopKey' => 'ITP-000000',
        'orderId' => '01',
        'amount' => '10.00',
        'daysToExpire' => 2,
    ];
});


describe('[UNIT] RequestObj/PayshopInitRequest', function () {

    it('creates a request object successfully', function () {

        $requestObj = new PayshopInitRequest(...$this->dataArray);

        expect($requestObj)->toBeInstanceOf(PayshopInitRequest::class);
        expect($requestObj->toPayload())->not->toBeNull();
    });

    // payshopKey
    it('throws an exception when payshopKey empty string', function () {
        $this->dataArray['payshopKey'] = '';
        new PayshopInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "is required.");

    it('throws an exception if payshopKey invalid format A1A-000000', function () {

        $this->dataArray['payshopKey'] = 'A1A-000000';
        new PayshopInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "must be a valid key");

    // orderId
    it('throws an exception when orderId empty string', function () {
        $this->dataArray['orderId'] = '';
        new PayshopInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "is required.");

    it('throws an exception when fields exceed max length', function () {

        $this->dataArray['orderId'] = str_repeat('1', 26);
        new PayshopInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "length must be equal or less than 25 characters.");

    // amount
    it('throws an exception when amount empty string', function () {
        $this->dataArray['amount'] = '';
        new PayshopInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "is required.");

    it('throws an exception when amount invalid format', function () {

        $this->dataArray['amount'] = '10,00';
        new PayshopInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "must be a positive decimal number with a '.' as the separator");

    it('throws an exception when amount is negative', function () {

        $this->dataArray['amount'] = '-10.00';
        new PayshopInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "must be a positive decimal number with a '.' as the separator");

    it('throws an exception when amount exceed max length', function () {

        $this->dataArray['amount'] = str_repeat('1', 8) . '.00';
        new PayshopInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "length must be equal or less than 10 characters.");


    // daysToExpire
    it('throws an exception when daysToExpire is negative', function () {
        $this->dataArray['daysToExpire'] = -1;
        new PayshopInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "must be equal or greater than 0.");

    it('throws an exception when daysToExpire exceed max value', function () {
        $this->dataArray['daysToExpire'] = 366;
        new PayshopInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "must be equal or less than 365.");

    // toPayload
    it('returns the array version of the object', function () {
        $requestObj = new PayshopInitRequest(...$this->dataArray);
        expect($requestObj->toPayload())->toEqual([
            "payshopkey" => $this->dataArray['payshopKey'],
            "id"      => $this->dataArray['orderId'],
            "valor"       => $this->dataArray['amount'],
            "validade"  => DateTools::getFutureDate($this->dataArray['daysToExpire'])->format('Ymd'),
        ]);
    });
});
