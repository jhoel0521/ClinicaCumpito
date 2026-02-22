<?php

use App\ValueObjects\Measurements\HeadCircumference;

describe('HeadCircumference', function () {
    test('can create valid head circumference', function () {
        $circumference = HeadCircumference::make(55.5);
        expect($circumference->value())->toBe(55.5);
    });

    test('rounds to 2 decimal places', function () {
        $circumference = HeadCircumference::make(55.123456);
        expect($circumference->value())->toBe(55.12);
    });

    test('accepts string and int values', function () {
        $circ1 = HeadCircumference::make('52.8');
        $circ2 = HeadCircumference::make(58);

        expect($circ1->value())->toBe(52.8);
        expect($circ2->value())->toBe(58.0);
    });

    test('can convert to inches', function () {
        $circumference = HeadCircumference::make(56.0);
        expect(round($circumference->inInches(), 2))->toBe(22.05);
    });

    test('throws on too small head circumference', function () {
        HeadCircumference::make(19.9);
    })->throws(\InvalidArgumentException::class);

    test('throws on too large head circumference', function () {
        HeadCircumference::make(80.1);
    })->throws(\InvalidArgumentException::class);

    test('can compare circumferences', function () {
        $circ1 = HeadCircumference::make(55.0);
        $circ2 = HeadCircumference::make(55.001);
        $circ3 = HeadCircumference::make(60.0);

        expect($circ1->equals($circ2))->toBeTrue();
        expect($circ1->equals($circ3))->toBeFalse();
    });

    test('can convert to string', function () {
        $circumference = HeadCircumference::make(52.6);
        expect((string) $circumference)->toBe('52.6 cm');
    });
});
