<?php

namespace Ifthenpay\PaymentGateway;

use Ifthenpay\PaymentGateway\Exception\ConfigException;
use Ifthenpay\PaymentGateway\Http\Http;
use Ifthenpay\PaymentGateway\Service\MbwayService;
use Ifthenpay\PaymentGateway\Interface\Http\HttpClientInterface;
use Ifthenpay\PaymentGateway\Service\ApiService;
use Ifthenpay\PaymentGateway\Service\CreditCardService;
use Ifthenpay\PaymentGateway\Service\MultibancoDynamicService;
use Ifthenpay\PaymentGateway\Service\MultibancoOfflineService;
use Ifthenpay\PaymentGateway\Service\PaymentService;
use Ifthenpay\PaymentGateway\Service\PayshopService;
use Ifthenpay\PaymentGateway\Service\PixService;
use Ifthenpay\PaymentGateway\Service\CofidisService;
use Ifthenpay\PaymentGateway\Service\PayByLinkService;
use Ifthenpay\PaymentGateway\Service\WebhookService;
use Psr\Http\Client\ClientInterface;

class IfthenpayGateway
{
    private Config $config;
    private HttpClientInterface $httpClient;
    private ApiService $api;
    private WebhookService $webhookService;
    private PaymentService $paymentService;
    private MbwayService $mbway;
    private MultibancoDynamicService $multibancoDynamic;
    private MultibancoOfflineService $multibancoOffline;
    private PayshopService $payshop;
    private PixService $pix;
    private CreditCardService $creditCard;
    private CofidisService $cofidis;
    private PayByLinkService $payByLink;


    /**
     * Constructor for IfthenpayGateway.
     * @param array<string, mixed> $config Configuration array.
     * @param ClientInterface|null $httpClient Optional HTTP client.
     */
    public function __construct(
        array $config,
        ?ClientInterface $httpClient = null
    ) {
        $this->config = Config::fromArray($config);


        $psr17Factory     = new \Nyholm\Psr7\Factory\Psr17Factory();
        $this->httpClient = new Http(
            $httpClient ?? new \Http\Client\Curl\Client($psr17Factory, $psr17Factory),
            new \Nyholm\Psr7\Factory\Psr17Factory(),
            new \Nyholm\Psr7\Factory\Psr17Factory()
        );

        $this->api            = new ApiService($this->config, $this->httpClient);
        $this->webhookService = new WebhookService($this->config, $this->api);
        $this->paymentService = new PaymentService($this->config, $this->api);
    }



    /**
     * Returns the instance of the ApiService.
     *
     * @return ApiService
     */
    public function api(): ApiService
    {
        return $this->api;
    }



    /**
     * Returns the instance of the WebhookService.
     *
     * @return WebhookService
     */
    public function webhook(): WebhookService
    {
        return $this->webhookService;
    }



    /**
     * Returns the instance of the PaymentService.
     *
     * @return PaymentService
     */
    public function payment(): PaymentService
    {
        return $this->paymentService;
    }



    /**
     * Returns the instance of the MbwayService.
     *
     * @return MbwayService
     */
    public function mbway(): MbwayService
    {
        if (!isset($this->mbway)) {
            $this->validateConfig(['mbwayKey']);
            $this->mbway = new MbwayService($this->config, $this->api(), $this->webhookService, $this->paymentService);
        }

        return $this->mbway;
    }



    /**
     * Returns the instance of the PixService.
     *
     * @return PixService
     */
    public function pix(): PixService
    {
        if (!isset($this->pix)) {
            $this->validateConfig(['pixKey']);
            $this->pix = new PixService($this->config, $this->api(), $this->webhookService, $this->paymentService);
        }

        return $this->pix;
    }



    /**
     * Returns the instance of the MultibancoDynamicService.
     *
     * @return MultibancoDynamicService
     */
    public function multibancoDynamic(): MultibancoDynamicService
    {
        if (!isset($this->multibancoDynamic)) {
            $this->validateConfig(['multibancoDynamicKey']);
            $this->multibancoDynamic = new MultibancoDynamicService($this->config, $this->api(), $this->webhookService, $this->paymentService);
        }

        return $this->multibancoDynamic;
    }



    /**
     * Returns the instance of the MultibancoOfflineService.
     *
     * @return MultibancoOfflineService
     */
    public function multibancoOffline(): MultibancoOfflineService
    {
        if (!isset($this->multibancoOffline)) {
            $this->validateConfig(['multibancoOfflineEntity', 'multibancoOfflineSubEntity']);
            $this->multibancoOffline = new MultibancoOfflineService($this->config, $this->webhookService, $this->paymentService);
        }

        return $this->multibancoOffline;
    }



    /**
     * Returns the instance of the PayshopService.
     *
     * @return PayshopService
     */
    public function payshop(): PayshopService
    {
        if (!isset($this->payshop)) {
            $this->validateConfig(['payshopKey']);
            $this->payshop = new PayshopService($this->config, $this->api(), $this->webhookService, $this->paymentService);
        }

        return $this->payshop;
    }



    /**
     * Returns the instance of the CreditCardService.
     *
     * @return CreditCardService
     */
    public function creditCard(): CreditCardService
    {
        if (!isset($this->creditCard)) {
            $this->validateConfig(['creditCardKey']);
            $this->creditCard = new CreditCardService($this->config, $this->api(), $this->webhookService, $this->paymentService);
        }

        return $this->creditCard;
    }



    /**
     * Returns the instance of the CofidisService.
     *
     * @return CofidisService
     */
    public function cofidis(): CofidisService
    {
        if (!isset($this->cofidis)) {
            $this->validateConfig(['cofidisKey']);
            $this->cofidis = new CofidisService($this->config, $this->api(), $this->webhookService, $this->paymentService);
        }

        return $this->cofidis;
    }



    /**
     * Returns the instance of the PayByLinkService.
     *
     * @return PayByLinkService
     */
    public function payByLink(): PayByLinkService
    {
        if (!isset($this->payByLink)) {
            $this->validateConfig(['payByLinkKey']);
            $this->payByLink = new PayByLinkService($this->config, $this->api(), $this->webhookService, $this->paymentService);
        }

        return $this->payByLink;
    }

    /**
     * Validates that required configuration keys are present.
     * @param array<string> $requiredKeys The keys that must be present in the config.
     * @throws ConfigException if any required key is missing.
     */
    private function validateConfig(array $requiredKeys): void
    {
        foreach ($requiredKeys as $key) {
            if (empty($this->config->get($key))) {
                throw new ConfigException("Missing required config value: {$key}");
            }
        }
    }



    /**
     * Returns the configuration instance.
     *
     * @return Config
     */
    public function config(): Config
    {
        return $this->config;
    }
}
