<?php

use Ifthenpay\PaymentGateway\Config;

describe('[UNIT] IfthenpayGateway', function () {


    // beforeEach(function () {});



    it('should have a private constructor', function () {
        new Config([]);
    })->throws(Error::class, 'Call to private');


    it('instantiates from an array', function () {

        $config = Config::fromArray([
            'mbway' => [
                'key' => 'mbway_test_key'
            ],
        ]);

        expect($config)->toBeInstanceOf(Config::class);
    });

    it('throws exception when instantiating from array with invalid property', function () {
        Config::fromArray([
            'invalidProperty' => 'some_value'
        ]);
    })->throws(\InvalidArgumentException::class, "Property 'invalidProperty' does not exist in " . Config::class);

    it('throws exception when accessing property that has not been set', function () {
        $config = Config::fromArray([]);
        $config->mbwayKey();
    })->throws(\Error::class, 'must not be accessed before initialization');


    it('sets and gets all available properties at once', function () {

        $configArray = [
            'backofficeKey'   => '0000-0000-0000-0000',
            'antiPhishingKey' => 'abcde12345',
            'language'        => 'pt',
            'mbway'           => [
                'key'             => 'ITP-000001',
                'minutesToExpire' => 15,
            ],
            'multibancoDynamic' => [
                'key'          => 'ITP-000002',
                'daysToExpire' => 5,
            ],
            'multibancoOffline' => [
                'entity'       => '00000',
                'subEntity'    => '000',
                'daysToExpire' => 5,
            ],
            'payshop' => [
                'key'          => 'ITP-000003',
                'daysToExpire' => 5,
            ],
            'creditCard' => [
                'key'             => 'ITP-000004',
                'minutesToExpire' => 15,
                'successUrl'      => 'https://exampleurl.com/sucess',
                'errorUrl'        => 'https://exampleurl.com/error',
                'cancelUrl'       => 'https://exampleurl.com/cancel',
            ],
            'pix' => [
                'key'             => 'ITP-000005',
                'minutesToExpire' => 15,
            ],
            'cofidis' => [
                'key'             => 'ITP-000006',
                'returnUrl' => 'https://exampleurl.com/return',
                'minutesToExpire' => 60,
            ],
            'payByLink' => [
                'key'            => 'AITP-000000',
                'methodAccounts' => [
                    '00000'   => '000',
                    'MBWAY'   => 'ITP-000001',
                    'PAYSHOP' => 'ITP-000002',
                    'CCARD'   => 'ITP-000003',
                    'COFIDIS' => 'ITP-000004',
                    'GOOGLE'  => 'ITP-000005',
                    'APPLE'   => 'ITP-000006',
                    'PIX'     => 'ITP-000007',
                ],
                'defaultMethod' => 'CCARD',
                'daysToExpire'  => 60,
                'successUrl'    => 'https://youraddress.com/sucess',
                'errorUrl'      => 'https://youraddress.com/error',
                'cancelUrl'     => 'https://youraddress.com/cancel',
                'btnCloseUrl'   => 'https://youraddress.com/close',
                'btnCloseLabel' => 'Close',
            ],
        ];
        $config = Config::fromArray($configArray);

        expect($config->all())->toBe(
            [
                'backofficeKey'             => $configArray['backofficeKey'],
                'antiPhishingKey'           => $configArray['antiPhishingKey'],
                'language'                  => $configArray['language'],
                'mbwayKey'                  => $configArray['mbway']['key'],
                'mbwayMinutesToExpire'      => $configArray['mbway']['minutesToExpire'],
                'multibancoDynamicKey'      => $configArray['multibancoDynamic']['key'],
                'multibancoDynamicDaysToExpire' => $configArray['multibancoDynamic']['daysToExpire'],
                'multibancoOfflineEntity'    => $configArray['multibancoOffline']['entity'],
                'multibancoOfflineSubEntity' => $configArray['multibancoOffline']['subEntity'],
                'multibancoOfflineDaysToExpire' => $configArray['multibancoOffline']['daysToExpire'],
                'payshopKey'                => $configArray['payshop']['key'],
                'payshopDaysToExpire'       => $configArray['payshop']['daysToExpire'],
                'pixKey'                    => $configArray['pix']['key'],
                'pixMinutesToExpire'        => $configArray['pix']['minutesToExpire'],
                'creditCardKey'             => $configArray['creditCard']['key'],
                'creditCardSuccessUrl'      => $configArray['creditCard']['successUrl'],
                'creditCardCancelUrl'       => $configArray['creditCard']['cancelUrl'],
                'creditCardErrorUrl'        => $configArray['creditCard']['errorUrl'],
                'creditCardMinutesToExpire' => $configArray['creditCard']['minutesToExpire'],
                'cofidisKey'                => $configArray['cofidis']['key'],
                'cofidisReturnUrl'          => $configArray['cofidis']['returnUrl'],
                'cofidisMinutesToExpire'    => $configArray['cofidis']['minutesToExpire'],
                'payByLinkKey'            => $configArray['payByLink']['key'],
                'payByLinkMethodAccounts'  => $configArray['payByLink']['methodAccounts'],
                'payByLinkDefaultMethod'   => $configArray['payByLink']['defaultMethod'],
                'payByLinkIsOneTimePayment' => $configArray['payByLink']['isOneTimePayment'] ?? false,
                'payByLinkSuccessUrl'      => $configArray['payByLink']['successUrl'],
                'payByLinkErrorUrl'        => $configArray['payByLink']['errorUrl'],
                'payByLinkCancelUrl'       => $configArray['payByLink']['cancelUrl'],
                'payByLinkBtnCloseUrl'     => $configArray['payByLink']['btnCloseUrl'],
                'payByLinkBtnCloseLabel'   => $configArray['payByLink']['btnCloseLabel'],
                'payByLinkDaysToExpire'       => $configArray['payByLink']['daysToExpire'],
                'endpoints'                 => [
                    'mbway_init'       => 'https://api.ifthenpay.com/spg/payment/mbway',
                    'mbway_status'     => 'https://api.ifthenpay.com/spg/payment/mbway/status',
                    'multibanco_init'  => 'https://api.ifthenpay.com/multibanco/reference/init',
                    'payshop_init'     => 'https://ifthenpay.com/api/payshop/reference/',
                    'pix_init'         => 'https://api.ifthenpay.com/pix/init/',
                    'creditcard_init'  => 'https://api.ifthenpay.com/creditcard/init/',
                    'cofidis_init'     => 'https://api.ifthenpay.com/cofidis/init/',
                    'cofidis_status'   => 'https://api.ifthenpay.com/cofidis/status',
                    'paybylink_init'   => 'https://api.ifthenpay.com/gateway/pinpay/',
                    'paybylink_status' => 'https://api.ifthenpay.com/gateway/transaction/status/get',
                    'register_webhook' => 'https://ifthenpay.com/api/endpoint/callback/activation',
                    'list_payments'    => 'https://api.ifthenpay.com/v2/payments/read',
                ]
            ]
        );
    });


    it('sets all available properties and gets each speparately', function () {

        $configArray = [
            'backofficeKey'   => '0000-0000-0000-0000',
            'antiPhishingKey' => 'abcde12345',
            'language'        => 'pt',
            'mbway'           => [
                'key'             => 'ITP-000001',
                'minutesToExpire' => 15,
            ],
            'multibancoDynamic' => [
                'key'          => 'ITP-000002',
                'daysToExpire' => 5,
            ],
            'multibancoOffline' => [
                'entity'       => '00000',
                'subEntity'    => '000',
                'daysToExpire' => 5,
            ],
            'payshop' => [
                'key'          => 'ITP-000003',
                'daysToExpire' => 5,
            ],
            'creditCard' => [
                'key'             => 'ITP-000004',
                'minutesToExpire' => 15,
                'successUrl'      => 'https://exampleurl.com/sucess',
                'errorUrl'        => 'https://exampleurl.com/error',
                'cancelUrl'       => 'https://exampleurl.com/cancel',
            ],
            'pix' => [
                'key'             => 'ITP-000005',
                'minutesToExpire' => 15,
            ],
            'cofidis' => [
                'key'             => 'ITP-000006',
                'minutesToExpire' => 60,
            ],
            'payByLink' => [
                'key'            => 'AITP-000000',
                'methodAccounts' => [
                    '00000'   => '000',
                    'MBWAY'   => 'ITP-000001',
                    'PAYSHOP' => 'ITP-000002',
                    'CCARD'   => 'ITP-000003',
                    'COFIDIS' => 'ITP-000004',
                    'GOOGLE'  => 'ITP-000005',
                    'APPLE'   => 'ITP-000006',
                    'PIX'     => 'ITP-000007',
                ],
                'defaultMethod' => 'CCARD',
                'daysToExpire'  => 60,
                'successUrl'    => 'https://youraddress.com/sucess',
                'errorUrl'      => 'https://youraddress.com/error',
                'cancelUrl'     => 'https://youraddress.com/cancel',
                'btnCloseUrl'   => 'https://youraddress.com/close',
                'btnCloseLabel' => 'Close',
            ],
        ];
        $config = Config::fromArray($configArray);

        expect($config->backofficeKey())->toBe($configArray['backofficeKey'])
            ->and($config->antiPhishingKey())->toBe($configArray['antiPhishingKey'])
            ->and($config->language())->toBe($configArray['language'])
            ->and($config->mbwayKey())->toBe($configArray['mbway']['key'])
            ->and($config->mbwayMinutesToExpire())->toBe($configArray['mbway']['minutesToExpire'])
            ->and($config->multibancoDynamicKey())->toBe($configArray['multibancoDynamic']['key'])
            ->and($config->multibancoDynamicDaysToExpire())->toBe($configArray['multibancoDynamic']['daysToExpire'])
            ->and($config->multibancoOfflineEntity())->toBe($configArray['multibancoOffline']['entity'])
            ->and($config->multibancoOfflineSubEntity())->toBe($configArray['multibancoOffline']['subEntity'])
            ->and($config->multibancoOfflineDaysToExpire())->toBe($configArray['multibancoOffline']['daysToExpire'])
            ->and($config->payshopKey())->toBe($configArray['payshop']['key'])
            ->and($config->payshopDaysToExpire())->toBe($configArray['payshop']['daysToExpire'])
            ->and($config->pixKey())->toBe($configArray['pix']['key'])
            ->and($config->pixMinutesToExpire())->toBe($configArray['pix']['minutesToExpire'])
            ->and($config->creditCardKey())->toBe($configArray['creditCard']['key'])
            ->and($config->creditCardSuccessUrl())->toBe($configArray['creditCard']['successUrl'])
            ->and($config->creditCardCancelUrl())->toBe($configArray['creditCard']['cancelUrl'])
            ->and($config->creditCardErrorUrl())->toBe($configArray['creditCard']['errorUrl'])
            ->and($config->creditCardMinutesToExpire())->toBe($configArray['creditCard']['minutesToExpire'])
            ->and($config->cofidisKey())->toBe($configArray['cofidis']['key'])
            ->and($config->cofidisMinutesToExpire())->toBe($configArray['cofidis']['minutesToExpire'])
            ->and($config->payByLinkKey())->toBe($configArray['payByLink']['key'])
            ->and($config->payByLinkMethodAccounts())->toBe($configArray['payByLink']['methodAccounts'])
            ->and($config->payByLinkDefaultMethod())->toBe($configArray['payByLink']['defaultMethod'])
            ->and($config->payByLinkIsOneTimePayment())->toBe($configArray['payByLink']['isOneTimePayment'] ?? false)
            ->and($config->payByLinkSuccessUrl())->toBe($configArray['payByLink']['successUrl'])
            ->and($config->payByLinkErrorUrl())->toBe($configArray['payByLink']['errorUrl'])
            ->and($config->payByLinkCancelUrl())->toBe($configArray['payByLink']['cancelUrl'])
            ->and($config->payByLinkBtnCloseUrl())->toBe($configArray['payByLink']['btnCloseUrl'])
            ->and($config->payByLinkBtnCloseLabel())->toBe($configArray['payByLink']['btnCloseLabel'])
            ->and($config->payByLinkDaysToExpire())->toBe($configArray['payByLink']['daysToExpire'])
            ->and($config->endpoint('mbway_init'))->toBe('https://api.ifthenpay.com/spg/payment/mbway')
            ->and($config->endpoint('mbway_status'))->toBe('https://api.ifthenpay.com/spg/payment/mbway/status')
            ->and($config->endpoint('multibanco_init'))->toBe('https://api.ifthenpay.com/multibanco/reference/init')
            ->and($config->endpoint('payshop_init'))->toBe('https://ifthenpay.com/api/payshop/reference/')
            ->and($config->endpoint('pix_init'))->toBe('https://api.ifthenpay.com/pix/init/')
            ->and($config->endpoint('creditcard_init'))->toBe('https://api.ifthenpay.com/creditcard/init/')
            ->and($config->endpoint('cofidis_init'))->toBe('https://api.ifthenpay.com/cofidis/init/')
            ->and($config->endpoint('cofidis_status'))->toBe('https://api.ifthenpay.com/cofidis/status')
            ->and($config->endpoint('paybylink_init'))->toBe('https://api.ifthenpay.com/gateway/pinpay/')
            ->and($config->endpoint('paybylink_status'))->toBe('https://api.ifthenpay.com/gateway/transaction/status/get')
            ->and($config->endpoint('register_webhook'))->toBe('https://ifthenpay.com/api/endpoint/callback/activation')
            ->and($config->endpoint('list_payments'))->toBe('https://api.ifthenpay.com/v2/payments/read');
    });

    it('should return default value when getting non existing config key with "get"', function () {
        $config = Config::fromArray([]);
        $defaultValue = 'default_value';
        $result = $config->get('non_existing_key', $defaultValue);
        expect($result)->toBe($defaultValue);
    });
});
