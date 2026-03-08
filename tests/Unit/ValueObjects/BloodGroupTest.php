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

    test('throws on invalid blood group', function () {
        BloodGroup::make('C+');
    })->throws(\InvalidArgumentException::class);

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
