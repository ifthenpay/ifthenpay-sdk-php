<?php

use Ifthenpay\PaymentGateway\RequestObj\PayByLinkInitRequest;
use Ifthenpay\PaymentGateway\Utils\DateTools;

beforeEach(function () {

    $this->dataArray = [
        'payByLinkKey'    => 'AITP-000000',
        'orderId'         => '01',
        'amount'          => '10.00',
        'methodAccounts'  => 'MBWAY|ITP-000000;CCARD|ITP-000000',
        'successUrl'      => 'https://example.com/success',
        'errorUrl'        => 'https://example.com/error',
        'cancelUrl'       => 'https://example.com/cancel',
        'closeButtonUrl'  => 'https://example.com/close',
        'closeButtonLabel' => 'Close',
        'description'     => 'Test payment',
        'defaultMethod'   => 1,
        'daysToExpire'    => 7,
        'isOneTimePayment' => true,
        'language'        => 'pt'
    ];
});


describe('[UNIT] RequestObj/PayByLinkInitRequest', function () {

    it('should create PayByLinkInitRequest object successfully', function () {
        $requestObj = new PayByLinkInitRequest(...$this->dataArray);
        expect($requestObj)->toBeInstanceOf(PayByLinkInitRequest::class);
    });

    // payByLinkKey

    it('throws an exception when payByLinkKey empty string', function () {
        $this->dataArray['payByLinkKey'] = '';
        new PayByLinkInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "is required.");


    it('throws an exception if payByLinkKey invalid format', function () {
        $this->dataArray['payByLinkKey'] = 'ITP-000000';
        $requestObj = new PayByLinkInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "'payByLinkKey' must be a valid gateway key");

    // orderId
    it('throws an exception when orderId empty string', function () {
        $this->dataArray['orderId'] = '';
        new PayByLinkInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "is required.");

    it('throws an exception when orderId exceeds max length', function () {
        $this->dataArray['orderId'] = str_repeat('1', 26);
        new PayByLinkInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "length must be equal or less than 25 characters.");

    // amount
    it('throws an exception when amount empty string', function () {
        $this->dataArray['amount'] = '';
        new PayByLinkInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "is required.");

    it('throws an exception if amount invalid format', function () {
        $this->dataArray['amount'] = '10,00';
        $requestObj = new PayByLinkInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "must be a positive decimal number with a '.' as the separator (e.g. 10.50)");

    it('throws an exception if amount negative', function () {
        $this->dataArray['amount'] = '-10.00';
        $requestObj = new PayByLinkInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "must be a positive decimal number with a '.' as the separator (e.g. 10.50)");

    it('throws an exception when amount exceed max length', function () {
        $this->dataArray['amount'] = str_repeat('1', 8) . '.00';
        new PayByLinkInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "length must be equal or less than 10 characters.");

    // methodAccounts
    it('throws an exception when methodAccounts empty string', function () {
        $this->dataArray['methodAccounts'] = '';
        new PayByLinkInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "is required.");

    it('throws an exception if methodAccounts invalid format MBWAY|ITP-0000002', function () {
        $this->dataArray['methodAccounts'] = 'MBWAY|ITP-0000002';
        new PayByLinkInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "must be a valid method accounts string");

    it('throws an exception if methodAccounts invalid format MBWAY-ITP-000000', function () {
        $this->dataArray['methodAccounts'] = 'MBWAY-ITP-000000';
        new PayByLinkInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "must be a valid method accounts string");

    it('throws an exception if methodAccounts invalid format MBWAY-ITP-000000-CCARD|ITP-000000', function () {
        $this->dataArray['methodAccounts'] = 'MBWAY-ITP-000000-CCARD|ITP-000000';
        new PayByLinkInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "must be a valid method accounts string");

    it('throws an exception if methodAccounts invalid format MBWAY|ITP-000000;MBWAY|ITP-000000', function () {
        $this->dataArray['methodAccounts'] = 'MBWAY|ITP-000000;MBWAY|ITP-000000';
        new PayByLinkInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "must not contain repeated payment methods");


    // successUrl
    it('throws an exception if instantiating with successUrl not valid url', function () {

        $this->dataArray['successUrl'] = 'yourdomain.com/success';
        $requestObj = new PayByLinkInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "must be a valid URL.");

    it('throws an exception if instantiating with successUrl exceeding length', function () {

        $this->dataArray['successUrl'] = 'https://yourdomain.com/' . str_repeat('1', 2001 - 23);
        $requestObj = new PayByLinkInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "length must be equal or less than 2000 characters.");

    it('creates object with successUrl null', function () {

        $this->dataArray['successUrl'] = null;
        $requestObj = new PayByLinkInitRequest(...$this->dataArray);
        expect($requestObj->successUrl)->toEqual(null);
    });

    // errorUrl
    it('throws an exception if instantiating with errorUrl not valid url', function () {
        $this->dataArray['errorUrl'] = 'yourdomain.com/error';
        $requestObj = new PayByLinkInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "must be a valid URL.");

    it('throws an exception if instantiating with errorUrl exceeding length', function () {

        $this->dataArray['errorUrl'] = 'https://yourdomain.com/' . str_repeat('1', 2001 - 23);
        $requestObj = new PayByLinkInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "length must be equal or less than 2000 characters.");

    it('creates object with errorUrl null', function () {

        $this->dataArray['errorUrl'] = null;
        $requestObj = new PayByLinkInitRequest(...$this->dataArray);
        expect($requestObj->errorUrl)->toEqual(null);
    });

    // cancelUrl
    it('throws an exception if instantiating with cancelUrl not valid url', function () {
        $this->dataArray['cancelUrl'] = 'yourdomain.com/cancel';
        $requestObj = new PayByLinkInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "must be a valid URL.");

    it('throws an exception if instantiating with cancelUrl exceeding length', function () {

        $this->dataArray['cancelUrl'] = 'https://yourdomain.com/' . str_repeat('1', 2001 - 23);
        $requestObj = new PayByLinkInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "length must be equal or less than 2000 characters.");

    it('creates object with cancelUrl null', function () {

        $this->dataArray['cancelUrl'] = null;
        $requestObj = new PayByLinkInitRequest(...$this->dataArray);
        expect($requestObj->cancelUrl)->toEqual(null);
    });


    // closeButtonUrl
    it('throws an exception if instantiating with closeButtonUrl not valid url', function () {
        $this->dataArray['closeButtonUrl'] = 'yourdomain.com/closeButton';
        $requestObj = new PayByLinkInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "must be a valid URL.");

    it('throws an exception if instantiating with closeButtonUrl exceeding length', function () {

        $this->dataArray['closeButtonUrl'] = 'https://yourdomain.com/' . str_repeat('1', 2001 - 23);
        $requestObj = new PayByLinkInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "length must be equal or less than 2000 characters.");

    it('creates object with closeButtonUrl null', function () {

        $this->dataArray['closeButtonUrl'] = null;
        $requestObj = new PayByLinkInitRequest(...$this->dataArray);
        expect($requestObj->closeButtonUrl)->toEqual(null);
    });


    // closeButtonLabel
    it('throws an exception when closeButtonLabel exceeds max length', function () {
        $this->dataArray['closeButtonLabel'] = str_repeat('a', 51);
        new PayByLinkInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "length must be equal or less than 50 characters.");

    it('creates object with closeButtonLabel null', function () {

        $this->dataArray['closeButtonLabel'] = null;
        $requestObj = new PayByLinkInitRequest(...$this->dataArray);
        expect($requestObj->closeButtonLabel)->toEqual(null);
    });


    // description
    it('throws an exception when description exceeds max length', function () {
        $this->dataArray['description'] = str_repeat('a', 201);
        new PayByLinkInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "length must be equal or less than 200 characters.");

    it('creates object with description null', function () {

        $this->dataArray['description'] = null;
        $requestObj = new PayByLinkInitRequest(...$this->dataArray);
        expect($requestObj->description)->toEqual(null);
    });


    // defaultMethod
    it('throws an exception when defaultMethod less than min value', function () {
        $this->dataArray['defaultMethod'] = 0;
        new PayByLinkInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "must be equal or greater than 1.");

    it('throws an exception when defaultMethod greater than max value', function () {
        $this->dataArray['defaultMethod'] = 9;
        new PayByLinkInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "must be equal or less than 8.");

    it('creates object with defaultMethod null', function () {
        $this->dataArray['defaultMethod'] = null;
        $requestObj = new PayByLinkInitRequest(...$this->dataArray);
        expect($requestObj->defaultMethod)->toEqual(null);
    });


    // daysToExpire
    it('throws an exception when daysToExpire greater than max value', function () {
        $this->dataArray['daysToExpire'] = 366;
        new PayByLinkInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "must be equal or less than 365.");

    it('creates object with daysToExpire null', function () {
        $this->dataArray['daysToExpire'] = null;
        $requestObj = new PayByLinkInitRequest(...$this->dataArray);
        expect($requestObj->daysToExpire)->toEqual(null);
    });


    // isOneTimePayment
    it('creates object with isOneTimePayment false', function () {
        $this->dataArray['isOneTimePayment'] = false;
        $requestObj = new PayByLinkInitRequest(...$this->dataArray);
        expect($requestObj->isOneTimePayment)->toEqual(false);
    });

    // language
    it('creates object with language null', function () {
        $this->dataArray['language'] = null;
        $requestObj = new PayByLinkInitRequest(...$this->dataArray);
        expect($requestObj->language)->toEqual(null);
    });

    it('throws an exception if instantiating with language not in expected values', function () {

        $this->dataArray['language'] = 'de';
        $requestObj = new PayByLinkInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "must be one of the following values: pt");

    it('returns the array version of the object', function () {

        $requestObj = new PayByLinkInitRequest(...$this->dataArray);
        expect($requestObj->toPayload())->toEqual([
            "id"         => $this->dataArray['orderId'],
            "amount"          => $this->dataArray['amount'],
            "description"     => $this->dataArray['description'],
            "accounts"  => $this->dataArray['methodAccounts'],
            "selected_method"   => (string) $this->dataArray['defaultMethod'],
            "expiredate"    => DateTools::getFutureDate($this->dataArray['daysToExpire'])->format('Ymd'),
            "successUrl"      => $this->dataArray['successUrl'],
            "errorUrl"        => $this->dataArray['errorUrl'],
            "cancelUrl"       => $this->dataArray['cancelUrl'],
            "btnCloseUrl"   => $this->dataArray['closeButtonUrl'],
            "btnCloseLabel" => $this->dataArray['closeButtonLabel'],
            "otp" => (bool) $this->dataArray['isOneTimePayment'],
            "language"        => $this->dataArray['language']
        ]);
    });
});
