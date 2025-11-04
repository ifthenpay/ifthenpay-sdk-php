<?php

use Ifthenpay\PaymentGateway\IfthenpayGateway;

beforeEach(function () {

    $this->configArray = [
        'multibancoOffline' => [
            'entity'    => '12345',
            'subEntity' => '123',
        ],
        'backofficeKey'   => '1111-1111-1111-1111',
        'antiPhishingKey' => '1234123412341234',
    ];
});



describe('[UNIT - Multibanco Offline ] Service->initPayment()', function () {
    it('generates reference', function () {

        $ifthenpayGateway = new IfthenpayGateway($this->configArray);

        $multibancoOfflinePayment = $ifthenpayGateway->multibancoOffline()->initPayment('01', '20.00');
        expect($multibancoOfflinePayment->getReference())->toBeString()->toBe('123000125');
    });
});
