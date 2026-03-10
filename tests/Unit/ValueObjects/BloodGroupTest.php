<?php

use App\ValueObjects\BloodGroup;

describe('BloodGroup', function () {
    test('can create valid blood group', function () {
        $group = BloodGroup::make('O+');
        expect($group->value())->toBe('O+');
    });

    test('can create all blood groups', function () {
        $groups = ['O+', 'O-', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-'];

        foreach ($groups as $group) {
            expect(BloodGroup::make($group)->value())->toBe($group);
        }
    });

    test('acepta cualquier cadena como grupo sanguineo (incluye tipos raros)', function () {
        // BloodGroup no valida contra una lista fija para permitir tipos raros
        expect(BloodGroup::make('Bombay')->value())->toBe('Bombay');
        expect(BloodGroup::make('AB-')->value())->toBe('AB-');
        expect(BloodGroup::make('C+')->value())->toBe('C+');
    });

    test('can compare blood groups', function () {
        $group1 = BloodGroup::make('O+');
        $group2 = BloodGroup::make('O+');
        $group3 = BloodGroup::make('O-');

        expect($group1->equals($group2))->toBeTrue();
        expect($group1->equals($group3))->toBeFalse();
    });

    test('can convert to string', function () {
        $group = BloodGroup::make('AB-');
        expect((string) $group)->toBe('AB-');
    });
});
