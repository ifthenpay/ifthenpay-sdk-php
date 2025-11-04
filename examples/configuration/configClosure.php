<?php


return [
    'backofficeKey'   => '1111-1111-1111-1111',
    'antiPhishingKey' => 'a0a0a0a0a0a0aa0a0a0a',
    'language'        => 'pt',
    'mbway'           => [
        'key'             => 'ITP-000000',
        'minutesToExpire' => 4,
    ],
    'multibancoDynamic' => [
        'key'          => 'ITP-000000',
        'daysToExpire' => 3,
    ],
    'multibancoOffline' => [
        'entity'       => '11111',
        'subEntity'    => '111',
        'daysToExpire' => 3,
    ],
    'payshop' => [
        'key'          => 'ITP-000000',
        'daysToExpire' => 3,
    ],
    'creditCard' => [
        'key'             => 'ITP-000000',
        'minutesToExpire' => 15,
        'successUrl'      => 'https://youraddress.com/sucess.php',
        'errorUrl'        => 'https://youraddress.com/error.php',
        'cancelUrl'       => 'https://youraddress.com/cancel.php',
    ],
    'pix' => [
        'key'             => 'ITP-000000',
        'minutesToExpire' => 15,
    ],
    'cofidis' => [
        'key'             => 'ITP-000000',
        'minutesToExpire' => 60,
        'returnUrl'       => 'https://youraddress.com/return.php',
    ],
    'payByLink' => [
        'key'            => 'ITPG-000000', // Gateway key, not the same as other account key
        'methodAccounts' => [
            '11111'   => '111',
            'MBWAY'   => 'ITP-000000',
            'PAYSHOP' => 'ITP-000000',
            'CCARD'   => 'ITP-000000',
            'COFIDIS' => 'ITP-000000',
            'GOOGLE'  => 'ITP-000000',
            'APPLE'   => 'ITP-000000',
            'PIX'     => 'ITP-000000',
        ],
        'defaultMethod'    => 'CCARD', // MBWAY, MULTIBANCO_DYNAMIC, MULTIBANCO_STATIC, PAYSHOP, CREDIT_CARD, COFIDIS, GOOGLE, APPLE, PIX
        'daysToExpire'     => 3,
        'isOneTimePayment' => true,
        'successUrl'       => 'https://youraddress.com/sucess.php',
        'errorUrl'         => 'https://youraddress.com/error.php',
        'cancelUrl'        => 'https://youraddress.com/cancel.php',
        'btnCloseUrl'      => 'https://youraddress.com',
        'btnCloseLabel'    => 'Close',
    ],
];
