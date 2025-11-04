<?php

use Ifthenpay\PaymentGateway\RequestObj\PixInitRequest;

beforeEach(function () {

    $this->dataArray = [
        'pixKey' => 'ITP-000000',
        'orderId' => '01',
        'amount' => '10.00',
        'cpf' => '111.111.111-11',
        'name' => 'John Doe',
        'email' => 'youremail@mail.com',
        'mobileNumber' => '912345678',
        'redirectUrl' => 'https://your-redirect-url.com',
        'description' => 'Test Description',
    ];
});



describe('[UNIT] RequestObj/PixInitRequest', function () {

    it('creates a request object successfully', function () {

        $requestObj = new PixInitRequest(...$this->dataArray);

        expect($requestObj)->toBeInstanceOf(PixInitRequest::class);
        expect($requestObj->toPayload())->not->toBeNull();
    });

    // pixKey
    it('throws an exception when pixKey empty string', function () {
        $this->dataArray['pixKey'] = '';
        new PixInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "is required.");

    it('throws an exception if pixKey invalid format A1A-000000', function () {

        $this->dataArray['pixKey'] = 'A1A-000000';
        new PixInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "must be a valid key");

    // orderId
    it('throws an exception when orderId empty string', function () {
        $this->dataArray['orderId'] = '';
        new PixInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "is required.");

    it('throws an exception when orderId exceed max length', function () {

        $this->dataArray['orderId'] = str_repeat('1', 26);
        new PixInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "length must be equal or less than 25 characters.");

    // amount
    it('throws an exception when amount empty string', function () {
        $this->dataArray['amount'] = '';
        new PixInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "is required.");

    it('throws an exception when amount invalid format', function () {

        $this->dataArray['amount'] = '10,00';
        new PixInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "must be a positive decimal number with a '.' as the separator");

    it('throws an exception when amount is negative', function () {

        $this->dataArray['amount'] = '-10.00';
        new PixInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "must be a positive decimal number with a '.' as the separator");

    it('throws an exception when amount exceed max length', function () {

        $this->dataArray['amount'] = str_repeat('1', 8) . '.00';
        new PixInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "length must be equal or less than 10 characters.");


    // cpf
    it('throws an exception when cpf empty string', function () {
        $this->dataArray['cpf'] = '';
        new PixInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "is required.");

    it('throws an exception when cpf invalid format v1 55555555555', function () {

        $this->dataArray['cpf'] = '55555555555';
        new PixInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "must be a valid CPF in the format (e.g. 111.111.111-11)");

    it('throws an exception when cpf invalid format v2 111-111.111-11', function () {

        $this->dataArray['cpf'] = '111-111.111-11';
        new PixInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "must be a valid CPF in the format (e.g. 111.111.111-11)");

    it('throws an exception when cpf invalid format v3 111.111.111.11', function () {

        $this->dataArray['cpf'] = '111.111.111.11';
        new PixInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "must be a valid CPF in the format (e.g. 111.111.111-11)");


    it('throws an exception when cpf invalid format v4 111.111.111-1f', function () {

        $this->dataArray['cpf'] = '111.111.111-1f';
        new PixInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "must be a valid CPF in the format (e.g. 111.111.111-11)");


    // name
    it('throws an exception when name empty string', function () {
        $this->dataArray['name'] = '';
        new PixInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "is required.");

    it('throws an exception when name exceed max length', function () {

        $this->dataArray['name'] = str_repeat('a', 151);
        new PixInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "length must be equal or less than 150 characters.");

    // email
    it('throws an exception when email empty string', function () {
        $this->dataArray['email'] = '';
        new PixInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "is required.");

    it('throws an exception when email invalid format', function () {

        $this->dataArray['email'] = 'invalid-email-format';
        new PixInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "must be a valid email address.");

    it('throws an exception when email exceed max length', function () {
        $this->dataArray['email'] = str_repeat('a', 64) . '@' . str_repeat('a', 27) . 'email.com';
        new PixInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "'email' length must be equal or less than 100 characters.");


    // mobileNumber
    it('throws an exception when mobileNumber empty string', function () {

        $this->dataArray['mobileNumber'] = '';
        new PixInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "'mobileNumber' is required.");

    it('throws an exception when mobileNumber has invalid format', function () {

        $this->dataArray['mobileNumber'] = '123ABC';
        new PixInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "must be a valid mobile number in the format");

    // redirectUrl
    it('throws an exception when redirectUrl empty string', function () {
        $this->dataArray['redirectUrl'] = '';
        new PixInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "is required.");

    it('throws an exception if instantiating with redirectUrl not valid url', function () {

        $this->dataArray['redirectUrl'] = '1';
        $requestObj = new PixInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "must be a valid URL.");

    it('throws an exception if instantiating with redirectUrl exceeding length', function () {

        $this->dataArray['redirectUrl'] = 'https://yourdomain.com/' . '' . str_repeat('a', 180);
        $requestObj = new PixInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "length must be equal or less than 200 characters.");

    // description
    it('throws an exception when description exceed max length', function () {

        $this->dataArray['description'] = str_repeat('a', 101);
        new PixInitRequest(...$this->dataArray);
    })->throws(InvalidArgumentException::class, "'description' length must be equal or less than 100 characters.");



    // nullable fields
    it('creates object with nullable properties', function () {
        $this->dataArray['description'] = null;
        $requestObj = new PixInitRequest(...$this->dataArray);
        expect($requestObj)->toBeInstanceOf(PixInitRequest::class);
        expect($requestObj->description)->toEqual(null);
    });


    it('returns the array version of the object', function () {
        $requestObj = new PixInitRequest(...$this->dataArray);
        expect($requestObj->toPayload())->toEqual([
            "orderId"      => $this->dataArray['orderId'],
            "amount"       => $this->dataArray['amount'],
            "customerCPF"  => $this->dataArray['cpf'],
            "customerName"  => $this->dataArray['name'],
            "customerEmail"        => $this->dataArray['email'],
            "customerPhone" => $this->dataArray['mobileNumber'],
            "redirectUrl"  => $this->dataArray['redirectUrl'],
            "description"  => $this->dataArray['description']
        ]);
    });
});
