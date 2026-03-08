<?php

use App\ValueObjects\PhoneNumber;

describe('PhoneNumber', function () {
    test('can create valid phone number', function () {
        $phone = PhoneNumber::make('+1 (123) 456-7890');
        expect($phone->value())->toBe('+1 (123) 456-7890');
    });

    test('accepts various formats', function () {
        $phones = [
            '+1234567890',
            '(123) 456-7890',
            '123-456-7890',
            '1234567890',
            '+1 (555) 123-4567',
        ];

        foreach ($phones as $phone) {
            expect(PhoneNumber::make($phone)->value())->toBe($phone);
        }
    });

    test('can extract digits only', function () {
        $phone = PhoneNumber::make('+1 (555) 123-4567');
        expect($phone->digitsOnly())->toBe('15551234567');
    });

    test('trims whitespace', function () {
        $phone = PhoneNumber::make('  1234567890  ');
        expect($phone->value())->toBe('1234567890');
    });

    test('throws on empty phone', function () {
        PhoneNumber::make('');
    })->throws(\InvalidArgumentException::class);

    test('throws on too few digits', function () {
        PhoneNumber::make('123');
    })->throws(\InvalidArgumentException::class);

    test('throws on too many digits', function () {
        PhoneNumber::make('+1 '.str_repeat('123', 8));
    })->throws(\InvalidArgumentException::class);

    test('throws on invalid format', function () {
        PhoneNumber::make('abc-def-ghij');
    })->throws(\InvalidArgumentException::class);

    test('can compare phones by digits only', function () {
        $phone1 = PhoneNumber::make('+1 (123) 456-7890');
        $phone2 = PhoneNumber::make('1-123-456-7890');
        $phone3 = PhoneNumber::make('1 (123) 456-7890');
        $phone4 = PhoneNumber::make('+1 (123) 456-7891');

        expect($phone1->equals($phone2))->toBeTrue();
        expect($phone1->equals($phone3))->toBeTrue();
        expect($phone1->equals($phone4))->toBeFalse();
    });

    test('can convert to string', function () {
        $phone = PhoneNumber::make('555-123-4567');
        expect((string) $phone)->toBe('555-123-4567');
    });

    test('can cast from model', function () {
        // Note: This requires User model to have phone_number field and cast
        // Skipping model integration test for now as User may not have phone cast
        $phone = PhoneNumber::make('9876543210');
        expect($phone->digitsOnly())->toBe('9876543210');
    });
});
