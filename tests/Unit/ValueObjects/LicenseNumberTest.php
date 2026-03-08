<?php

use App\ValueObjects\LicenseNumber;

describe('LicenseNumber', function () {
    test('can create valid license number', function () {
        $license = LicenseNumber::make('ABC-12345');
        expect($license->value())->toBe('ABC-12345');
    });

    test('trims whitespace', function () {
        $license = LicenseNumber::make('  ABC-12345  ');
        expect($license->value())->toBe('ABC-12345');
    });

    test('allows alphanumeric with hyphen and slash', function () {
        $licenses = ['123456', 'ABC-DE-FGH', 'MED/2025/001', 'LIC-2025/ABC'];

        foreach ($licenses as $lic) {
            expect(LicenseNumber::make($lic)->value())->toBe($lic);
        }
    });

    test('throws on empty license', function () {
        LicenseNumber::make('');
    })->throws(\InvalidArgumentException::class);

    test('throws on too short license', function () {
        LicenseNumber::make('ABC');
    })->throws(\InvalidArgumentException::class);

    test('throws on too long license', function () {
        LicenseNumber::make(str_repeat('A', 51));
    })->throws(\InvalidArgumentException::class);

    test('throws on invalid characters', function () {
        LicenseNumber::make('ABC@123#456');
    })->throws(\InvalidArgumentException::class);

    test('can compare case insensitively', function () {
        $license1 = LicenseNumber::make('ABC-12345');
        $license2 = LicenseNumber::make('abc-12345');
        $license3 = LicenseNumber::make('XYZ-12345');

        expect($license1->equals($license2))->toBeTrue();
        expect($license1->equals($license3))->toBeFalse();
    });

    test('can convert to string', function () {
        $license = LicenseNumber::make('MED-2025-001');
        expect((string) $license)->toBe('MED-2025-001');
    });
});
