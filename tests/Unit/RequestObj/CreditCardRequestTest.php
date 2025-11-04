<?php

use Ifthenpay\PaymentGateway\RequestObj\CreditCardInitRequest;

beforeEach(function () {

    $this->dataArray = [
        'creditCardKey' => 'ITP-000000',
        'orderId' => '01',
        'amount' => '20.00',
        'successUrl' => 'https://yourdomain.com/success',
        'errorUrl' => 'https://yourdomain.com/error',
        'cancelUrl' => 'https://yourdomain.com/cancel',
        'language' => 'pt',
    ];
});



describe('[UNIT] RequestObj/CreditCardInitRequest', function () {
    it('creates a request object successfully', function () {

        $requestObj = new CreditCardInitRequest(...$this->dataArray);

        expect($requestObj)->toBeInstanceOf(CreditCardInitRequest::class);
        expect($requestObj->toPayload())->not->toBeNull();
    });

    it('throws an exception if instantiating with key length greater', function () {

        $this->dataArray['creditCardKey'] = 'ITP-0000000';
        $requestObj = new CreditCardInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "must be a valid key");

    it('throws an exception if instantiating with key length lesser', function () {

        $this->dataArray['creditCardKey'] = 'ITP-00000';
        $requestObj = new CreditCardInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "must be a valid key");

    it('throws an exception if instantiating with key not correct format ITP-100000', function () {

        $this->dataArray['creditCardKey'] = 'ITP-0A0000';
        $requestObj = new CreditCardInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "must be a valid key");

    it('throws an exception if instantiating with key not correct format ITP-0A0000', function () {

        $this->dataArray['creditCardKey'] = 'ITP-0A0000';
        $requestObj = new CreditCardInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "must be a valid key");




    it('throws an exception if instantiating with orderId exceeding length', function () {

        $this->dataArray['orderId'] = '11112222233334444455556660';
        $requestObj = new CreditCardInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "'orderId' length must be equal or less than 25 characters.");


    it('throws an exception if instantiating with amount not in expected decimal format 10,00', function () {

        $this->dataArray['amount'] = '10,00';
        $requestObj = new CreditCardInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "must be a positive decimal number with a '.' as the separator");




    it('throws an exception if instantiating with amount not in expected decimal format 10.000', function () {

        $this->dataArray['amount'] = '10.000';
        $requestObj = new CreditCardInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "must be a positive decimal number with a '.' as the separator");

    it('throws an exception if instantiating with amount not in expected decimal format 10.00f', function () {

        $this->dataArray['amount'] = '10.00f';
        $requestObj = new CreditCardInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "must be a positive decimal number with a '.' as the separator");

    it('throws an exception if instantiating with amount exceeding length', function () {

        $this->dataArray['amount'] = '10000000.00';
        $requestObj = new CreditCardInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "'amount' length must be equal or less than 10 characters.");


    // successUrl
    it('throws an exception if instantiating with successUrl not valid url', function () {

        $this->dataArray['successUrl'] = '1';
        $requestObj = new CreditCardInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "must be a valid URL.");

    it('throws an exception if instantiating with successUrl exceeding length', function () {

        $this->dataArray['successUrl'] = 'https://yourdomain.com/' . '11111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111';
        $requestObj = new CreditCardInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "length must be equal or less than 200 characters.");

    it('creates object with successUrl null', function () {

        $this->dataArray['successUrl'] = null;
        $requestObj = new CreditCardInitRequest(...$this->dataArray);
        expect($requestObj->successUrl)->toEqual(null);
    });


    // errorUrl
    it('throws an exception if instantiating with errorUrl not valid url', function () {

        $this->dataArray['errorUrl'] = '1';
        $requestObj = new CreditCardInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "must be a valid URL.");

    it('throws an exception if instantiating with errorUrl exceeding length', function () {

        $this->dataArray['errorUrl'] = 'https://yourdomain.com/' . '11111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111';
        $requestObj = new CreditCardInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "length must be equal or less than 200 characters.");

    it('creates object with errorUrl null', function () {

        $this->dataArray['errorUrl'] = null;
        $requestObj = new CreditCardInitRequest(...$this->dataArray);
        expect($requestObj->errorUrl)->toEqual(null);
    });



    // cancelUrl
    it('throws an exception if instantiating with cancelUrl not valid url', function () {

        $this->dataArray['cancelUrl'] = '1';
        $requestObj = new CreditCardInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "must be a valid URL.");

    it('throws an exception if instantiating with cancelUrl exceeding length', function () {

        $this->dataArray['cancelUrl'] = 'https://yourdomain.com/' . '11111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111';
        $requestObj = new CreditCardInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "length must be equal or less than 200 characters.");

    it('creates object with cancelUrl null', function () {

        $this->dataArray['cancelUrl'] = null;
        $requestObj = new CreditCardInitRequest(...$this->dataArray);
        expect($requestObj->cancelUrl)->toEqual(null);
    });


    // language


    it('creates object with language null', function () {

        $this->dataArray['language'] = null;
        $requestObj = new CreditCardInitRequest(...$this->dataArray);
        expect($requestObj->language)->toEqual(null);
    });


    it('throws an exception if instantiating with language not in expected values', function () {

        $this->dataArray['language'] = 'de';
        $requestObj = new CreditCardInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "must be one of the following values: pt");

    it('returns the array version of the object', function () {

        $requestObj = new CreditCardInitRequest(...$this->dataArray);
        expect($requestObj->toPayload())->toEqual([
            "orderId"    => '01',
            "amount"     => '20.00',
            "successUrl" => 'https://yourdomain.com/success',
            "errorUrl"   => 'https://yourdomain.com/error',
            "cancelUrl"  => 'https://yourdomain.com/cancel',
            "language"   => 'pt',
        ]);
    });
});
