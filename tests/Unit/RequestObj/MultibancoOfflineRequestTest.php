<?php

use Ifthenpay\PaymentGateway\RequestObj\MultibancoOfflineRequest;

beforeEach(function () {

    $this->dataArray = [
        'entity'    => '12345',
        'subEntity' => '123',
        'orderId'   => '01',
        'amount'    => '20.00',
    ];
});



describe('[UNIT] RequestObj/MultibancoOfflineRequest', function () {
    it('creates a request object', function () {

        $requestObj = new MultibancoOfflineRequest(...$this->dataArray);

        expect($requestObj)->toBeInstanceOf(MultibancoOfflineRequest::class);
        expect($requestObj->toPayload())->not->toBeNull();
    });


    it('throws an exception if instantiating with entity length greater', function () {

        $this->dataArray['entity'] = '123455';
        $requestObj = new MultibancoOfflineRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "'entity' must be a valid Multibanco Offline entity in the format (e.g. 12345)");

    it('throws an exception if instantiating with entity length lesser', function () {

        $this->dataArray['entity'] = '1234';
        $requestObj = new MultibancoOfflineRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "'entity' must be a valid Multibanco Offline entity in the format (e.g. 12345)");

    it('throws an exception if instantiating with entity not numeric', function () {

        $this->dataArray['entity'] = '1234w';
        $requestObj = new MultibancoOfflineRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "'entity' must be a valid Multibanco Offline entity in the format (e.g. 12345)");


    it('throws an exception if instantiating with subEntity greater', function () {

        $this->dataArray['subEntity'] = '1';
        $requestObj = new MultibancoOfflineRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "'subEntity' must be a valid Multibanco Offline subentity in the format (e.g. 12 or 123)");

    it('throws an exception if instantiating with subEntity length lesser', function () {

        $this->dataArray['subEntity'] = '1234';
        $requestObj = new MultibancoOfflineRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "'subEntity' must be a valid Multibanco Offline subentity in the format (e.g. 12 or 123)");

    it('throws an exception if instantiating with subEntity not numeric', function () {

        $this->dataArray['subEntity'] = '12w';
        $requestObj = new MultibancoOfflineRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "'subEntity' must be a valid Multibanco Offline subentity in the format (e.g. 12 or 123)");


    it('throws an exception if instantiating with orderId exceeding length', function () {

        $this->dataArray['orderId'] = '11112222233334444455556660';
        $requestObj = new MultibancoOfflineRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "'orderId' length must be equal or less than 25 characters.");


    it('throws an exception if instantiating with amount not in expected decimal format 10,00', function () {

        $this->dataArray['amount'] = '10,00';
        $requestObj = new MultibancoOfflineRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "must be a positive decimal number with a '.' as the separator");

    it('throws an exception if instantiating with amount not in expected decimal format 10.000', function () {

        $this->dataArray['amount'] = '10.000';
        $requestObj = new MultibancoOfflineRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "must be a positive decimal number with a '.' as the separator");

    it('throws an exception if instantiating with amount not in expected decimal format 10.00f', function () {

        $this->dataArray['amount'] = '10.00f';
        $requestObj = new MultibancoOfflineRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "must be a positive decimal number with a '.' as the separator");

    it('throws an exception if instantiating with amount exceeding length', function () {

        $this->dataArray['amount'] = '10000000.00';
        $requestObj = new MultibancoOfflineRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "'amount' length must be equal or less than 10 characters.");



    it('returns the array version of the object', function () {

        $requestObj = new MultibancoOfflineRequest(...$this->dataArray);
        expect($requestObj->toPayload())->toEqual([
            "entity"     => $this->dataArray['entity'],
            "subEntity"  => $this->dataArray['subEntity'],
            "orderId"    => $this->dataArray['orderId'],
            "amount"     => $this->dataArray['amount'],
        ]);
    });
});
