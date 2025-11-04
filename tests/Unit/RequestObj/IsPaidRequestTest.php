<?php

use Ifthenpay\PaymentGateway\RequestObj\IsPaidRequest;

beforeEach(function () {

    $this->dataArray = [
        'backofficeKey' => '1111-1111-1111-1111',
        'transactionId' => 'TX1234567890',
        'amount'        => '0.50',
        'orderId'       => '01',
        'dateStart'     => '01-01-2024 00:00:00',
        'dateEnd'       => '31-01-2024 23:59',
    ];
});


describe('[UNIT] RequestObj/IsPaidRequest', function () {
    it('creates a request object successfully', function () {

        $requestObj = new IsPaidRequest(...$this->dataArray);

        expect($requestObj)->toBeInstanceOf(IsPaidRequest::class);
        expect($requestObj->toPayload())->not->toBeNull();
    });

    // backofficeKey
    it('throws an exception when backofficeKey empty string', function () {
        $this->dataArray['backofficeKey'] = '';
        new IsPaidRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "is required.");

    it('throws an exception if backofficeKey invalid format 1111-1111-1111-111f', function () {

        $this->dataArray['backofficeKey'] = '1111-1111-1111-111f';
        new IsPaidRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "must be a valid backoffice key in the format");

    it('throws an exception if backofficeKey invalid format 1111-1111-1111', function () {

        $this->dataArray['backofficeKey'] = '1111-1111-1111';
        new IsPaidRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "must be a valid backoffice key in the format");

    it('throws an exception if backofficeKey invalid format ITP-000000', function () {

        $this->dataArray['backofficeKey'] = 'ITP-000000';
        new IsPaidRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "must be a valid backoffice key in the format");

    // reference
    it('throws an exception when reference exceed max length', function () {
        $this->dataArray['reference'] = '1111222223333444445555';
        new IsPaidRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "'reference' length must be equal or less than 20 characters.");

    // transactionId
    it('throws an exception when transactionId exceed max length', function () {
        $this->dataArray['transactionId'] = '1111222223333444445555';
        new IsPaidRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "'transactionId' length must be equal or less than 20 characters.");

    // amount
    it('throws an exception when amount have invalid format', function () {

        $this->dataArray['amount'] = 'invalid_amount';
        new IsPaidRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "must be a positive decimal number with a '.' as the separator");

    // orderId
    it('throws an exception when orderId exceed max length', function () {

        $this->dataArray['orderId'] = '11112222233334444455556660';
        new IsPaidRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "'orderId' length must be equal or less than 25 characters.");

    // dateStart
    it('throws an exception when dateStart has invalid format', function () {
        $this->dataArray['dateStart'] = '2024/01/01 00:00:00';
        new IsPaidRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "'dateStart' must be a valid date in the format d-m-Y H:i:s.");

    // dateEnd
    it('throws an exception when dateEnd has invalid format', function () {
        $this->dataArray['dateEnd'] = '2024/01/31 23:59';
        new IsPaidRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "'dateEnd' must be a valid date in the format d-m-Y H:i.");

    it('returns the array version of the object', function () {
        $requestObj = new IsPaidRequest(...$this->dataArray);
        expect($requestObj->toPayload())->toEqual([
            "boKey"     => $this->dataArray['backofficeKey'],
            "reference" => null,
            "requestId" => $this->dataArray['transactionId'],
            "amount"    => $this->dataArray['amount'],
            "orderId"   => $this->dataArray['orderId'],
            "dateStart" => $this->dataArray['dateStart'],
            "dateEnd"   => $this->dataArray['dateEnd'],
        ]);
    });
});
