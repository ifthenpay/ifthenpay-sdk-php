<?php

use Ifthenpay\PaymentGateway\Utils\StringTools;


describe('[UNIT] Utils StringTools', function () {

    it('addQueryStringVars add query string variables to a url string', function () {
        $url = 'https://example.com/api/resource';
        $params = [
            'param1' => 'value1',
            'param2' => 'value2',
            'param3' => 'value3',
        ];
        $result = StringTools::addQueryStringVars($url, $params);
        expect($result)->toBe('https://example.com/api/resource?param1=value1&param2=value2&param3=value3');
    });

    it('addQueryStringVars generates queryString without base url', function () {
        $url = '';
        $params = [
            'param1' => 'value1',
            'param2' => 'value2',
        ];
        $result = StringTools::addQueryStringVars($url, $params);
        expect($result)->toBe('?param1=value1&param2=value2');
    });
});
