<?php

use App\ValueObjects\Measurements\Weight;

describe('Weight', function () {
    test('can create valid weight', function () {
        $weight = Weight::make(70.5);
        expect($weight->value())->toBe(70.5);
    });

    test('rounds to 2 decimal places', function () {
        $weight = Weight::make(70.123456);
        expect($weight->value())->toBe(70.12);
    });

    test('accepts string and int values', function () {
        $weight1 = Weight::make('65.8');
        $weight2 = Weight::make(75);

        expect($weight1->value())->toBe(65.8);
        expect($weight2->value())->toBe(75.0);
    });

    test('can convert to grams', function () {
        $weight = Weight::make(2.5);
        expect($weight->inGrams())->toBe(2500.0);
    });

    test('can convert to pounds', function () {
        $weight = Weight::make(70.0);
        expect(round($weight->inPounds(), 2))->toBe(154.32);
    });

    test('throws on too light weight', function () {
        Weight::make(0.05);
    })->throws(\InvalidArgumentException::class);

    test('throws on too heavy weight', function () {
        Weight::make(300.1);
    })->throws(\InvalidArgumentException::class);

    test('can compare weights', function () {
        $weight1 = Weight::make(70.0);
        $weight2 = Weight::make(70.001);
        $weight3 = Weight::make(75.0);

        expect($weight1->equals($weight2))->toBeTrue();
        expect($weight1->equals($weight3))->toBeFalse();
    });

    test('can convert to string', function () {
        $weight = Weight::make(65.25);
        expect((string) $weight)->toBe('65.25 kg');
    });
});
