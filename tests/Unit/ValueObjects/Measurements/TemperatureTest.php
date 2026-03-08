<?php

use App\ValueObjects\Measurements\Temperature;

describe('Temperature', function () {
    test('can create valid temperature', function () {
        $temp = Temperature::make(37.5);
        expect($temp->value())->toBe(37.5);
    });

    test('rounds to 2 decimal places', function () {
        $temp = Temperature::make(37.123456);
        expect($temp->value())->toBe(37.12);
    });

    test('accepts string and int values', function () {
        $temp1 = Temperature::make('36.5');
        $temp2 = Temperature::make(36);

        expect($temp1->value())->toBe(36.5);
        expect($temp2->value())->toBe(36.0);
    });

    test('identifies normal temperature', function () {
        $normal = Temperature::make(37.0);
        expect($normal->isNormal())->toBeTrue();
        expect($normal->isFever())->toBeFalse();
        expect($normal->isHypothermia())->toBeFalse();
    });

    test('identifies fever', function () {
        $fever = Temperature::make(38.5);
        expect($fever->isFever())->toBeTrue();
        expect($fever->isNormal())->toBeFalse();
    });

    test('identifies hypothermia', function () {
        $hypo = Temperature::make(35.5);
        expect($hypo->isHypothermia())->toBeTrue();
        expect($hypo->isNormal())->toBeFalse();
    });

    test('throws on too low temperature', function () {
        Temperature::make(34.9);
    })->throws(\InvalidArgumentException::class);

    test('throws on too high temperature', function () {
        Temperature::make(42.1);
    })->throws(\InvalidArgumentException::class);

    test('can compare temperatures', function () {
        $temp1 = Temperature::make(37.0);
        $temp2 = Temperature::make(37.001);
        $temp3 = Temperature::make(38.0);

        expect($temp1->equals($temp2))->toBeTrue();
        expect($temp1->equals($temp3))->toBeFalse();
    });

    test('can convert to string', function () {
        $temp = Temperature::make(37.5);
        expect((string) $temp)->toBe('37.5°C');
    });
});
