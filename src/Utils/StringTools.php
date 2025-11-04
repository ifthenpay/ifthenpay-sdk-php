<?php

namespace Ifthenpay\PaymentGateway\Utils;

class StringTools
{
    /**
     * Appends query string variables to a given URL, looks for ? or & to append correctly.
     * @param string $urlString The base URL to which query parameters will be added.
     * @param array<string, string> $params An associative array of query parameters to add.
     * @return string The URL with the appended query string parameters.
     */
    public static function addQueryStringVars(string $urlString, array $params): string
    {
        $queryString = '';
        foreach ($params as $key => $value) {
            $queryString .= $key . '=' . $value . '&';
        }
        $queryString = rtrim($queryString, '&');

        $separator = strpos($urlString, '?') === false ? '?' : '&';
        return $urlString . $separator . $queryString;
    }
}
