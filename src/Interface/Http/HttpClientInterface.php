<?php

namespace Ifthenpay\PaymentGateway\Interface\Http;

use Psr\Http\Message\ResponseInterface;

interface HttpClientInterface
{
    public function post(string $url, array $data): ResponseInterface;
    public function get(string $url): ResponseInterface;
}
