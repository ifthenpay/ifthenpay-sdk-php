<?php

namespace Ifthenpay\PaymentGateway\Http;

use Ifthenpay\PaymentGateway\Interface\Http\HttpClientInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\ResponseInterface;

/*
 * Class Http wrapping a PSR-18 HTTP Client and a PSR-17 Request Factory
 */

class Http implements HttpClientInterface
{
    public function __construct(
        private ClientInterface $client,
        private RequestFactoryInterface $requestFactory,
        private StreamFactoryInterface $streamFactory,
    ) {
    }

    /**
     * Sends a POST request to the specified URL with the given data.
     * @param string $url The URL to send the POST request to.
     * @param array<string, mixed> $data The data to include in the POST request
     * @param array<string, string> $headers Optional headers to include in the request.
     * @return ResponseInterface The response from the server.
     */
    public function post(string $url, array $data, array $headers = []): ResponseInterface
    {
        $request = $this->requestFactory->createRequest('POST', $url)
            ->withHeader('Content-Type', 'application/json');

        foreach ($headers as $key => $value) {
            $request = $request->withHeader($key, $value);
        }


        $stream  = $this->streamFactory->createStream(json_encode($data) ?: '');
        $request = $request->withBody($stream);

        return $this->client->sendRequest($request);
    }


    /**
     * Sends a GET request to the specified URL.
     * @param string $url The URL to send the GET request to.
     * @param array<string, string> $headers Optional headers to include in the request.
     * @return ResponseInterface The response from the server.
     */
    public function get(string $url, array $headers = []): ResponseInterface
    {
        $request = $this->requestFactory->createRequest('GET', $url)
            ->withHeader('Content-Type', 'application/json');

        foreach ($headers as $key => $value) {
            $request = $request->withHeader($key, $value);
        }

        return $this->client->sendRequest($request);
    }
}
