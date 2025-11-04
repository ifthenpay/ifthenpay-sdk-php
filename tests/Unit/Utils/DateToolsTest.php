<?php

use Ifthenpay\PaymentGateway\Utils\DateTools;


describe('[UNIT] Utils DateTools', function () {

    test('convertDate returns correctly formatted date', function () {
        $result = DateTools::convertDate('Y-m-d H:i', 'd/m/Y H:i', '2024-05-20 15:30');
        expect($result)->toBe('20/05/2024 15:30');
    });

    test('convertDate returns empty string for invalid date', function () {
        $result = DateTools::convertDate('Y-m-d', 'd/m/Y', 'invalid-date');
        expect($result)->toBe('');
    });

    test('isPastDate returns true for past date', function () {
        $past = new DateTimeImmutable('yesterday', new DateTimeZone(DateTools::TIMEZONE));
        $result = DateTools::isPastDate($past);

        expect($result)->toBeTrue();
    });

    test('isPastDate returns false for future date', function () {
        $future = new DateTimeImmutable('tomorrow', new DateTimeZone(DateTools::TIMEZONE));
        $result = DateTools::isPastDate($future);

        expect($result)->toBeFalse();
    });

    test('getFutureDate adds days correctly', function () {
        $date = DateTools::getFutureDate(2);
        $expected = new DateTimeImmutable('now +2 days', new DateTimeZone(DateTools::TIMEZONE));

        // Compare formatted values to avoid microsecond mismatches
        expect($date->format('Y-m-d'))->toBe($expected->format('Y-m-d'));
    });

    test('getFutureDate adds hours and minutes correctly', function () {
        $date = DateTools::getFutureDate(0, 2, 30);
        $expected = new DateTimeImmutable('now +2 hours +30 minutes', new DateTimeZone(DateTools::TIMEZONE));

        expect($date->format('Y-m-d H:i'))->toBe($expected->format('Y-m-d H:i'));
    });

    test('getFutureDate sets time when setHourMin is true', function () {
        $date = DateTools::getFutureDate(0, 10, 15, true);

        expect($date->format('H:i'))->toBe('10:15');
    });

    test('getTimeStamp returns current DateTimeImmutable with correct timezone', function () {
        $timestamp = DateTools::getTimeStamp();

        expect($timestamp)->toBeInstanceOf(DateTimeImmutable::class)
            ->and($timestamp->getTimezone()->getName())->toBe(DateTools::TIMEZONE);
    });
});
