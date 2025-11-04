<?php

namespace Ifthenpay\PaymentGateway\Service;

use Ifthenpay\PaymentGateway\Config;
use Ifthenpay\PaymentGateway\Interface\Http\HttpClientInterface;
use Ifthenpay\PaymentGateway\RequestObj\CofidisInitRequest;
use Ifthenpay\PaymentGateway\RequestObj\CreditCardInitRequest;
use Ifthenpay\PaymentGateway\RequestObj\IsPaidRequest;
use Ifthenpay\PaymentGateway\RequestObj\MbwayInitRequest;
use Ifthenpay\PaymentGateway\RequestObj\MultibancoDynamicInitRequest;
use Ifthenpay\PaymentGateway\RequestObj\PayByLinkInitRequest;
use Ifthenpay\PaymentGateway\RequestObj\PayshopInitRequest;
use Ifthenpay\PaymentGateway\RequestObj\PixInitRequest;
use Ifthenpay\PaymentGateway\RequestObj\RegisterWebhookRequest;
use Psr\Http\Message\ResponseInterface;

class ApiService
{
    private Config $config;
    private HttpClientInterface $httpClient;

    public function __construct(
        Config $config,
        HttpClientInterface $httpClient,
    ) {
        $this->config     = $config;
        $this->httpClient = $httpClient;
    }



    public function registerWebhook(RegisterWebhookRequest $request): ResponseInterface
    {
        return $this->httpClient->post($this->config->endpoint('register_webhook'), $request->toPayload());
    }



    public function initMbwayPayment(MbwayInitRequest $request): ResponseInterface
    {
        return $this->httpClient->post($this->config->endpoint('mbway_init'), $request->toPayload());
    }



    public function initMultibancoPayment(MultibancoDynamicInitRequest $request): ResponseInterface
    {
        return $this->httpClient->post($this->config->endpoint('multibanco_init'), $request->toPayload());
    }



    public function initPayshopPayment(PayshopInitRequest $request): ResponseInterface
    {
        return $this->httpClient->post($this->config->endpoint('payshop_init'), $request->toPayload());
    }



    public function initPixPayment(PixInitRequest $request): ResponseInterface
    {
        return $this->httpClient->post($this->config->endpoint('pix_init') . $request->pixKey, $request->toPayload());
    }



    public function initCreditCardPayment(CreditCardInitRequest $request): ResponseInterface
    {
        return $this->httpClient->post($this->config->endpoint('creditcard_init') . $request->creditCardKey, $request->toPayload());
    }



    public function initCofidisPayment(CofidisInitRequest $request): ResponseInterface
    {
        return $this->httpClient->post($this->config->endpoint('cofidis_init') . $request->cofidisKey, $request->toPayload());
    }



    public function initPayByLinkPayment(PayByLinkInitRequest $request): ResponseInterface
    {
        return $this->httpClient->post($this->config->endpoint('paybylink_init') . $request->payByLinkKey, $request->toPayload());
    }



    public function getMbwayPaymentStatus(string $mbwayKey, string $transactionId): ResponseInterface
    {
        return $this->httpClient->get($this->config->endpoint('mbway_status') . '?' . http_build_query(['mbWayKey' => $mbwayKey, 'requestId' => $transactionId]));
    }



    public function getCofidisPaymentStatus(string $cofidisKey, string $transactionId): ResponseInterface
    {
        return $this->httpClient->post($this->config->endpoint('cofidis_status'), [
            'cofidisKey' => $cofidisKey,
            'requestId'  => $transactionId,
        ]);
    }



    public function getPayByLinkPaymentStatus(string $transactionId): ResponseInterface
    {
        return $this->httpClient->get($this->config->endpoint('paybylink_status') . '?' . http_build_query(['transactionId' => $transactionId]));
    }



    public function isPaid(IsPaidRequest $request): ResponseInterface
    {
        return $this->httpClient->post($this->config->endpoint('list_payments'), $request->toPayload());
    }
}
