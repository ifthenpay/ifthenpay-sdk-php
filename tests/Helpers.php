<?php

use Ifthenpay\PaymentGateway\Config;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

function mockClientIsPaidHttp(bool $isPaid)
{
    $bodyStr = $isPaid ? '{ "message": "OK", "payments": [{ "amount": 10 }], "status": 200 }' : '{ "message": "OK", "payments": [], "status": 200 }';

    $mockStream = Mockery::mock(StreamInterface::class);
    $mockStream->shouldReceive('__toString')
        ->andReturn($bodyStr);

    $mockResponse = Mockery::mock(ResponseInterface::class);
    $mockResponse->shouldReceive('getStatusCode')->andReturn(200);
    $mockResponse->shouldReceive('getBody')->andReturn($mockStream);

    $endpoint = (Config::fromArray([]))->endpoint('list_payments');

    $mockClient = Mockery::mock(ClientInterface::class);
    $mockClient->shouldReceive('sendRequest')
        ->once()
        ->with(mockedPostRequest($endpoint, []))
        ->andReturn($mockResponse);

    return $mockClient;
}



function mockClientRegisterWebhook(int $numberOfCalls = 1)
{
    $mockResponse = Mockery::mock(ResponseInterface::class);
    $mockResponse->shouldReceive('getStatusCode')->andReturn(200);
    $mockResponse->shouldReceive('getBody->getContents')->andReturn('OK: Callback registered successfully');

    $endpoint = (Config::fromArray([]))->endpoint('register_webhook');

    $mockClient = Mockery::mock(ClientInterface::class);

    if ($numberOfCalls === 1) {
        $mockClient->shouldReceive('sendRequest')
            ->once()
            ->with(mockedPostRequest($endpoint, []))
            ->andReturn($mockResponse);
    } else {
        $mockClient->shouldReceive('sendRequest')
            ->times($numberOfCalls)
            ->with(mockedPostRequest($endpoint, []))
            ->andReturn($mockResponse);
    }

    return $mockClient;
}


function mockedPostRequest(string $expectedPath, array $expectedPayload)
{
    return Mockery::on(function (RequestInterface $request) use ($expectedPayload, $expectedPath) {
        // Check method
        expect($request->getMethod())->toBe('POST');

        // Check URL path
        expect((string) $request->getUri())->toBe($expectedPath);

        // Check body (assumes JSON)
        $body = (string) $request->getBody();
        $data = json_decode($body, true);
        expect($data)->toMatchArray($expectedPayload);

        return true; // Must return true for Mockery::on
    });
}

function mockedGetRequest(string $expectedPath)
{
    return Mockery::on(function (RequestInterface $request) use ($expectedPath) {
        // Check method
        expect($request->getMethod())->toBe('GET');

        // Check URL path
        expect((string) $request->getUri())->toBe($expectedPath);

        return true; // Must return true for Mockery::on
    });
}
