<?php

use App\ValueObjects\Measurements\Height;

describe('Height', function () {
    test('can create valid height', function () {
        $height = Height::make(175.5);
        expect($height->value())->toBe(175.5);
    });

    test('rounds to 2 decimal places', function () {
        $height = Height::make(175.123456);
        expect($height->value())->toBe(175.12);
    });

    test('accepts string and int values', function () {
        $height1 = Height::make('165.8');
        $height2 = Height::make(180);

        expect($height1->value())->toBe(165.8);
        expect($height2->value())->toBe(180.0);
    });

    test('can convert to meters', function () {
        $height = Height::make(175.0);
        expect($height->inMeters())->toBe(1.75);
    });

    test('can convert to inches', function () {
        $height = Height::make(180.0);
        expect(round($height->inInches(), 2))->toBe(70.87);
    });

    test('throws on too short height', function () {
        Height::make(0.05);
    })->throws(\InvalidArgumentException::class);

    test('throws on too tall height', function () {
        Height::make(250.1);
    })->throws(\InvalidArgumentException::class);

    test('can compare heights', function () {
        $height1 = Height::make(175.0);
        $height2 = Height::make(175.001);
        $height3 = Height::make(180.0);

        expect($height1->equals($height2))->toBeTrue();
        expect($height1->equals($height3))->toBeFalse();
    });

    test('can convert to string', function () {
        $height = Height::make(162.8);
        expect((string) $height)->toBe('162.8 cm');
    });
});
