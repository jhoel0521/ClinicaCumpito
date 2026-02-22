<?php

use App\ValueObjects\Gender;

describe('Gender', function () {
    test('can create male gender', function () {
        $gender = Gender::make('M');
        expect($gender->value())->toBe('M');
        expect($gender->isMale())->toBeTrue();
        expect($gender->isFemale())->toBeFalse();
    });

    test('can create female gender', function () {
        $gender = Gender::make('F');
        expect($gender->value())->toBe('F');
        expect($gender->isFemale())->toBeTrue();
        expect($gender->isMale())->toBeFalse();
    });

    test('throws on invalid gender', function () {
        Gender::make('X');
    })->throws(\InvalidArgumentException::class);

    test('can compare genders', function () {
        $male1 = Gender::make('M');
        $male2 = Gender::make('M');
        $female = Gender::make('F');

        expect($male1->equals($male2))->toBeTrue();
        expect($male1->equals($female))->toBeFalse();
    });

    test('can convert to spanish string', function () {
        expect((string) Gender::make('M'))->toBe('Masculino');
        expect((string) Gender::make('F'))->toBe('Femenino');
    });
});
