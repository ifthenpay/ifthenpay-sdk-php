<?php

use Ifthenpay\PaymentGateway\IfthenpayGateway;
use Ifthenpay\PaymentGateway\Exception\ConfigException;
use Ifthenpay\PaymentGateway\Service\ApiService;
use Ifthenpay\PaymentGateway\Service\WebhookService;
use Ifthenpay\PaymentGateway\Service\PaymentService;
use Ifthenpay\PaymentGateway\Service\MbwayService;
use Ifthenpay\PaymentGateway\Service\PixService;
use Ifthenpay\PaymentGateway\Service\MultibancoDynamicService;
use Ifthenpay\PaymentGateway\Service\MultibancoOfflineService;
use Ifthenpay\PaymentGateway\Service\PayshopService;
use Ifthenpay\PaymentGateway\Service\CreditCardService;
use Ifthenpay\PaymentGateway\Service\CofidisService;
use Psr\Http\Client\ClientInterface;

describe('[UNIT] IfthenpayGateway', function () {


    beforeEach(function () {

        $this->configArray = [
            'multibancoOffline' => [
                'entity'    => '11111',
                'subEntity' => '111',
            ],
            'mbway' => [
                'key' => 'ITP-000000'
            ],
            'payshop' => [
                'key' => 'ITP-000000'
            ],
            'multibancoDynamic' => [
                'key' => 'ITP-000000'
            ],
            'pix' => [
                'key' => 'ITP-000000'
            ],
            'creditCard' => [
                'key' => 'ITP-000000'
            ],
            'cofidis'         => [
                'key' => 'ITP-000000'
            ]
        ];

        $this->mockHttpClient = Mockery::mock(ClientInterface::class);
    });



    it('instantiates the gateway correctly with default config', function () {
        $gateway = new IfthenpayGateway($this->configArray, $this->mockHttpClient);

        expect($gateway)->toBeInstanceOf(IfthenpayGateway::class)
            ->and($gateway->api())->toBeInstanceOf(ApiService::class)
            ->and($gateway->webhook())->toBeInstanceOf(WebhookService::class)
            ->and($gateway->payment())->toBeInstanceOf(PaymentService::class);
    });

    it('instantiates mbway service (lazily created and cached)', function () {
        $gateway = new IfthenpayGateway($this->configArray, $this->mockHttpClient);

        $service1 = $gateway->mbway();
        $service2 = $gateway->mbway();

        expect($service1)
            ->toBeInstanceOf(MbwayService::class)
            ->and($service1)->toBe($service2); // same instance (cached)
    });

    it('instantiates pix service', function () {
        $gateway = new IfthenpayGateway($this->configArray, $this->mockHttpClient);

        expect($gateway->pix())->toBeInstanceOf(PixService::class);
    });

    it('instantiates multibanco dynamic service', function () {
        $gateway = new IfthenpayGateway($this->configArray, $this->mockHttpClient);

        expect($gateway->multibancoDynamic())->toBeInstanceOf(MultibancoDynamicService::class);
    });

    it('instantiates multibanco offline service', function () {
        $gateway = new IfthenpayGateway($this->configArray, $this->mockHttpClient);

        expect($gateway->multibancoOffline())->toBeInstanceOf(MultibancoOfflineService::class);
    });

    it('instantiates payshop service', function () {
        $gateway = new IfthenpayGateway($this->configArray, $this->mockHttpClient);

        expect($gateway->payshop())->toBeInstanceOf(PayshopService::class);
    });

    it('instantiates credit card service', function () {
        $gateway = new IfthenpayGateway($this->configArray, $this->mockHttpClient);

        expect($gateway->creditCard())->toBeInstanceOf(CreditCardService::class);
    });

    it('instantiates cofidis service', function () {
        $gateway = new IfthenpayGateway($this->configArray, $this->mockHttpClient);

        expect($gateway->cofidis())->toBeInstanceOf(CofidisService::class);
    });

    it('throws exception when instantiating mbway without required config', function () {
        $badConfig = $this->configArray;
        unset($badConfig['mbway']['key']);

        $gateway = new IfthenpayGateway($badConfig, $this->mockHttpClient);

        $gateway->mbway();
    })->throws(ConfigException::class, 'Missing required config value: mbwayKey');

    it('throws exception when instantiating payshop without required config', function () {
        $badConfig = $this->configArray;
        unset($badConfig['payshop']['key']);

        $gateway = new IfthenpayGateway($badConfig, $this->mockHttpClient);

        $gateway->payshop();
    })->throws(ConfigException::class, 'Missing required config value');

    it('throws exception when instantiating multibanco dynamic without required config', function () {
        $badConfig = $this->configArray;
        unset($badConfig['multibancoDynamic']['key']);

        $gateway = new IfthenpayGateway($badConfig, $this->mockHttpClient);

        $gateway->multibancoDynamic();
    })->throws(ConfigException::class, 'Missing required config value');

    it('throws exception when instantiating multibanco offline without required config entity', function () {
        $badConfig = $this->configArray;
        unset($badConfig['multibancoOffline']['entity']);

        $gateway = new IfthenpayGateway($badConfig, $this->mockHttpClient);

        $gateway->multibancoOffline();
    })->throws(ConfigException::class, 'Missing required config value');

    it('throws exception when instantiating multibanco offline without required config subEntity', function () {
        $badConfig = $this->configArray;
        unset($badConfig['multibancoOffline']['subEntity']);

        $gateway = new IfthenpayGateway($badConfig, $this->mockHttpClient);

        $gateway->multibancoOffline();
    })->throws(ConfigException::class, 'Missing required config value');

    it('throws exception when instantiating pix without required config', function () {
        $badConfig = $this->configArray;
        unset($badConfig['pix']['key']);

        $gateway = new IfthenpayGateway($badConfig, $this->mockHttpClient);

        $gateway->pix();
    })->throws(ConfigException::class, 'Missing required config value');

    it('throws exception when instantiating credit card without required config', function () {
        $badConfig = $this->configArray;
        unset($badConfig['creditCard']['key']);

        $gateway = new IfthenpayGateway($badConfig, $this->mockHttpClient);

        $gateway->creditCard();
    })->throws(ConfigException::class, 'Missing required config value');

    it('throws exception when instantiating cofidis without required config', function () {
        $badConfig = $this->configArray;
        unset($badConfig['cofidis']['key']);

        $gateway = new IfthenpayGateway($badConfig, $this->mockHttpClient);

        $gateway->cofidis();
    })->throws(ConfigException::class, 'Missing required config value');
});
