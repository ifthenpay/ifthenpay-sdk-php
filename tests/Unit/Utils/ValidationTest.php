<?php

use Ifthenpay\PaymentGateway\Utils\Validation;



describe('[UNIT] Utils Validation', function () {

    it('validates nullable field', function () {
        $data = ['desc' => null];
        $rules = ['desc' => 'nullable'];
        expect(fn() => Validation::validate($data, $rules))->not->toThrow(Exception::class);
    });

    it('throws on nullable field with value', function () {
        $data = ['desc' => '10,00'];
        $rules = ['desc' => 'nullable|regex_money'];

        expect(fn() => Validation::validate($data, $rules))->toThrow(InvalidArgumentException::class, "must be a positive decimal number with a '.' as the separator");
    });

    it('validates required and string fields', function () {
        $data = ['name' => 'John'];
        $rules = ['name' => 'required'];
        expect(fn() => Validation::validate($data, $rules))->not->toThrow(Exception::class);
    });

    it('throws on missing required field', function () {
        $data = [];
        $rules = ['name' => 'required'];
        expect(fn() => Validation::validate($data, $rules))->toThrow(InvalidArgumentException::class, 'is required.');
    });

    it('throws on boolean wrong type', function () {
        $data = ['active' => 'yes'];
        $rules = ['active' => 'boolean'];
        expect(fn() => Validation::validate($data, $rules))->toThrow(InvalidArgumentException::class, 'must be a boolean.');
    });

    it('validates integer', function () {
        $data = ['age' => 25];
        $rules = ['age' => 'integer'];
        expect(fn() => Validation::validate($data, $rules))->not->toThrow(Exception::class);
    });

    it('throws on invalid integer', function () {
        $data = ['age' => '25'];
        $rules = ['age' => 'integer'];
        expect(fn() => Validation::validate($data, $rules))->toThrow(InvalidArgumentException::class, 'must be an integer.');
    });

    it('validates min_len', function () {
        $data = ['code' => 'abcd'];
        $rules = ['code' => 'min_len:3'];
        expect(fn() => Validation::validate($data, $rules))->not->toThrow(Exception::class);
    });

    it('throws on min_len', function () {
        $data = ['code' => 'ab'];
        $rules = ['code' => 'min_len:3'];
        expect(fn() => Validation::validate($data, $rules))->toThrow(InvalidArgumentException::class, 'length must be equal or greater than 3');
    });

    it('validates max_len', function () {
        $data = ['code' => 'abc'];
        $rules = ['code' => 'max_len:5'];
        expect(fn() => Validation::validate($data, $rules))->not->toThrow(Exception::class);
    });

    it('throws on max_len', function () {
        $data = ['code' => 'abcdef'];
        $rules = ['code' => 'max_len:5'];
        expect(fn() => Validation::validate($data, $rules))->toThrow(InvalidArgumentException::class, 'length must be equal or less than 5');
    });

    it('validates min_val', function () {
        $data = ['age' => 18];
        $rules = ['age' => 'min_val:18'];
        expect(fn() => Validation::validate($data, $rules))->not->toThrow(Exception::class);
    });

    it('throws on min_val', function () {
        $data = ['age' => 17];
        $rules = ['age' => 'min_val:18'];
        expect(fn() => Validation::validate($data, $rules))->toThrow(InvalidArgumentException::class, 'must be equal or greater than 18');
    });

    it('validates max_val', function () {
        $data = ['amount' => 5];
        $rules = ['amount' => 'max_val:10'];
        expect(fn() => Validation::validate($data, $rules))->not->toThrow(Exception::class);
    });

    it('throws on max_val', function () {
        $data = ['amount' => 15];
        $rules = ['amount' => 'max_val:10'];
        expect(fn() => Validation::validate($data, $rules))->toThrow(InvalidArgumentException::class, 'must be equal or less than 10');
    });

    it('validates numeric', function () {
        $data = ['entity' => '12345'];
        $rules = ['entity' => 'numeric'];
        expect(fn() => Validation::validate($data, $rules))->not->toThrow(Exception::class);
    });

    it('throws on non numeric', function () {
        $data = ['entity' => 'ten'];
        $rules = ['entity' => 'numeric'];
        expect(fn() => Validation::validate($data, $rules))->toThrow(InvalidArgumentException::class, 'must be numeric');
    });

    it('validates positive (type string)', function () {
        $data = ['amount' => '1.5'];
        $rules = ['amount' => 'positive'];
        expect(fn() => Validation::validate($data, $rules))->not->toThrow(Exception::class);
    });

    it('validates positive (type float)', function () {
        $data = ['amount' => 1];
        $rules = ['amount' => 'positive'];
        expect(fn() => Validation::validate($data, $rules))->not->toThrow(Exception::class);
    });

    it('throws on negative positive rule (type string)', function () {
        $data = ['amount' => '-1'];
        $rules = ['amount' => 'positive'];
        expect(fn() => Validation::validate($data, $rules))->toThrow(InvalidArgumentException::class, 'must be a positive number greater than 0');
    });

    it('throws on negative positive rule (type int)', function () {
        $data = ['amount' => -1];
        $rules = ['amount' => 'positive'];
        expect(fn() => Validation::validate($data, $rules))->toThrow(InvalidArgumentException::class, 'must be a positive number greater than 0');
    });

    it('validates url', function () {
        $data = ['website' => 'https://example.com'];
        $rules = ['website' => 'url'];
        expect(fn() => Validation::validate($data, $rules))->not->toThrow(Exception::class);
    });

    it('throws on invalid url', function () {
        $data = ['website' => 'example.com'];
        $rules = ['website' => 'url'];
        expect(fn() => Validation::validate($data, $rules))->toThrow(InvalidArgumentException::class, 'must be a valid URL');
    });

    it('validates email', function () {
        $data = ['email' => 'user@example.com'];
        $rules = ['email' => 'email'];
        expect(fn() => Validation::validate($data, $rules))->not->toThrow(Exception::class);
    });

    it('throws on invalid email', function () {
        $data = ['email' => 'not-an-email'];
        $rules = ['email' => 'email'];
        expect(fn() => Validation::validate($data, $rules))->toThrow(InvalidArgumentException::class, 'must be a valid email address');
    });

    it('validates regex_bokey', function () {
        $data = ['key' => '1111-1111-1111-1111'];
        $rules = ['key' => 'regex_bokey'];
        expect(fn() => Validation::validate($data, $rules))->not->toThrow(Exception::class);
    });

    it('throws on regex_bokey', function () {
        $data = ['key' => '1111111111111111'];
        $rules = ['key' => 'regex_bokey'];
        expect(fn() => Validation::validate($data, $rules))->toThrow(InvalidArgumentException::class, "must be a valid backoffice key in the format (e.g. 1111-1111-1111-1111)");
    });

    it('validates regex_key', function () {
        $data = ['key' => 'ITP-000000'];
        $rules = ['key' => 'regex_key'];
        expect(fn() => Validation::validate($data, $rules))->not->toThrow(Exception::class);
    });

    it('throws on regex_key', function () {
        $data = ['key' => 'AAA000000'];
        $rules = ['key' => 'regex_key'];
        expect(fn() => Validation::validate($data, $rules))->toThrow(InvalidArgumentException::class, "must be a valid key");
    });


    it('validates regex_gateway_key', function () {
        $data = ['key' => 'AITP-000000'];
        $rules = ['key' => 'regex_gateway_key'];
        expect(fn() => Validation::validate($data, $rules))->not->toThrow(Exception::class);
    });

    it('throws on regex_gateway_key', function () {
        $data = ['key' => 'ITP-000000'];
        $rules = ['key' => 'regex_gateway_key'];
        expect(fn() => Validation::validate($data, $rules))->toThrow(InvalidArgumentException::class, "must be a valid gateway key");
    });

    // regex_method_accounts
    it('validates regex_method_accounts', function () {
        $data = ['methods' => 'MBWAY|ITP-000000;PIX|BBB-000000'];
        $rules = ['methods' => 'regex_method_accounts'];
        expect(fn() => Validation::validate($data, $rules))->not->toThrow(Exception::class);
    });

    it('throws on regex_method_accounts', function () {
        $data = ['methods' => 'MBWAY-ITP-000000;PIX|BBB-000000'];
        $rules = ['methods' => 'regex_method_accounts'];
        expect(fn() => Validation::validate($data, $rules))->toThrow(InvalidArgumentException::class, "must be a valid method accounts string");
    });

    // regex_no_repeated_methods
    it('validates regex_no_repeated_methods', function () {
        $data = ['methods' => 'MBWAY|ITP-000000;PIX|BBB-000000'];
        $rules = ['methods' => 'regex_no_repeated_methods'];
        expect(fn() => Validation::validate($data, $rules))->not->toThrow(Exception::class);
    });

    it('throws on regex_no_repeated_methods', function () {
        $data = ['methods' => 'MBWAY|ITP-000000;MBWAY|BBB-000000'];
        $rules = ['methods' => 'regex_no_repeated_methods'];
        expect(fn() => Validation::validate($data, $rules))->toThrow(InvalidArgumentException::class, "must not contain repeated payment methods");
    });

    it('validates regex_money', function () {
        $data = ['price' => '12.50'];
        $rules = ['price' => 'regex_money'];
        expect(fn() => Validation::validate($data, $rules))->not->toThrow(Exception::class);
    });

    it('throws on regex_money', function () {
        $data = ['price' => '12,50'];
        $rules = ['price' => 'regex_money'];
        expect(fn() => Validation::validate($data, $rules))->toThrow(InvalidArgumentException::class, "must be a positive decimal number with a '.' as the separator");
    });

    it('throws on regex_money with negative', function () {
        $data = ['price' => '-12.50'];
        $rules = ['price' => 'regex_money'];
        expect(fn() => Validation::validate($data, $rules))->toThrow(InvalidArgumentException::class, "must be a positive decimal number with a '.' as the separator");
    });

    it('validates regex_date default format', function () {
        $data = ['date' => '20250128'];
        $rules = ['date' => 'regex_date'];
        expect(fn() => Validation::validate($data, $rules))->not->toThrow(Exception::class);
    });

    it('throws on invalid regex_date', function () {
        $data = ['date' => '28-01-2025'];
        $rules = ['date' => 'regex_date'];
        expect(fn() => Validation::validate($data, $rules))->toThrow(InvalidArgumentException::class, 'must be a valid date');
    });

    it('validates regex_date custom format', function () {
        $data = ['date' => '28-01-2025'];
        $rules = ['date' => 'regex_date:d-m-Y'];
        expect(fn() => Validation::validate($data, $rules))->not->toThrow(Exception::class);
    });

    it('throws on invalid regex_date custom format', function () {
        $data = ['date' => '2025/01/28'];
        $rules = ['date' => 'regex_date:d-m-Y'];
        expect(fn() => Validation::validate($data, $rules))->toThrow(InvalidArgumentException::class, 'must be a valid date');
    });

    it('validates regex_mobile', function () {
        $data = ['mobile' => '912345678'];
        $rules = ['mobile' => 'regex_mobile'];
        expect(fn() => Validation::validate($data, $rules))->not->toThrow(Exception::class);
    });

    it('throws on invalid regex_mobile', function () {
        $data = ['mobile' => '123456'];
        $rules = ['mobile' => 'regex_mobile'];
        expect(fn() => Validation::validate($data, $rules))->toThrow(InvalidArgumentException::class, 'must be a valid mobile number');
    });

    it('validates regex_cpf', function () {
        $data = ['cpf' => '111.111.111-11'];
        $rules = ['cpf' => 'regex_cpf'];
        expect(fn() => Validation::validate($data, $rules))->not->toThrow(Exception::class);
    });

    it('throws on invalid regex_cpf', function () {
        $data = ['cpf' => '11111111111'];
        $rules = ['cpf' => 'regex_cpf'];
        expect(fn() => Validation::validate($data, $rules))->toThrow(InvalidArgumentException::class, 'must be a valid CPF in the format (e.g. 111.111.111-11)');
    });

    it('validates regex_mb_expire_days', function () {
        $data = ['days' => '60'];
        $rules = ['days' => 'regex_mb_expire_days'];
        expect(fn() => Validation::validate($data, $rules))->not->toThrow(Exception::class);
    });

    it('throws on invalid regex_mb_expire_days', function () {
        $data = ['days' => '33'];
        $rules = ['days' => 'regex_mb_expire_days'];
        expect(fn() => Validation::validate($data, $rules))->toThrow(InvalidArgumentException::class, 'must be an integer matching 1 to 32 or 45, 60, 90, 120.');
    });

    it('validates regex_mb_entity', function () {
        $data = ['entity' => '12345'];
        $rules = ['entity' => 'regex_mb_entity'];
        expect(fn() => Validation::validate($data, $rules))->not->toThrow(Exception::class);
    });

    it('throws on invalid regex_mb_entity', function () {
        $data = ['entity' => '1234'];
        $rules = ['entity' => 'regex_mb_entity'];
        expect(fn() => Validation::validate($data, $rules))->toThrow(InvalidArgumentException::class, 'must be a valid Multibanco Offline entity in the format (e.g. 12345)');
    });

    it('validates regex_mb_subentity', function () {
        $data = ['subEntity' => '123'];
        $rules = ['subEntity' => 'regex_mb_subentity'];
        expect(fn() => Validation::validate($data, $rules))->not->toThrow(Exception::class);
    });

    it('throws on invalid regex_mb_subentity', function () {
        $data = ['subEntity' => 'fff'];
        $rules = ['subEntity' => 'regex_mb_subentity'];
        expect(fn() => Validation::validate($data, $rules))->toThrow(InvalidArgumentException::class, 'must be a valid Multibanco Offline subentity in the format (e.g. 12 or 123)');
    });


    it('validates enum MethodCode', function () {
        $data = ['method' => 'MBWAY'];
        $rules = ['method' => 'enum:MethodCode'];
        expect(fn() => Validation::validate($data, $rules))->not->toThrow(Exception::class);
    });

    it('throws on invalid enum MethodCode', function () {
        $data = ['method' => 'INVALID'];
        $rules = ['method' => 'enum:MethodCode'];
        expect(fn() => Validation::validate($data, $rules))->toThrow(InvalidArgumentException::class, 'must be one of the following values');
    });

    it('validates enum Language', function () {
        $data = ['lang' => 'pt'];
        $rules = ['lang' => 'enum:Language'];
        expect(fn() => Validation::validate($data, $rules))->not->toThrow(Exception::class);
    });

    it('throws on invalid enum Language', function () {
        $data = ['lang' => 'de'];
        $rules = ['lang' => 'enum:Language'];
        expect(fn() => Validation::validate($data, $rules))->toThrow(InvalidArgumentException::class, 'must be one of the following values');
    });

    it('throws on unknown enum parameter', function () {
        $data = ['value' => 'test'];
        $rules = ['value' => 'enum:UnknownEnum'];
        expect(fn() => Validation::validate($data, $rules))->toThrow(InvalidArgumentException::class, 'Unknown enum parameter');
    });

    it('throws on unknown rule', function () {
        $data = ['field' => 'value'];
        $rules = ['field' => 'unknown_rule'];
        expect(fn() => Validation::validate($data, $rules))->toThrow(InvalidArgumentException::class, 'Unknown validation rule');
    });
});
