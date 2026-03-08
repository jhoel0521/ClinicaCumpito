<?php

use App\ValueObjects\MedicalStatus;

describe('MedicalStatus', function () {
    test('can create positive status', function () {
        $status = MedicalStatus::make('Positive');
        expect($status->value())->toBe('Positive');
        expect($status->isPositive())->toBeTrue();
        expect($status->isNegative())->toBeFalse();
        expect($status->isNotTested())->toBeFalse();
    });

    test('can create negative status', function () {
        $status = MedicalStatus::make('Negative');
        expect($status->value())->toBe('Negative');
        expect($status->isNegative())->toBeTrue();
        expect($status->isPositive())->toBeFalse();
    });

    test('can create not tested status', function () {
        $status = MedicalStatus::make('Not tested');
        expect($status->value())->toBe('Not tested');
        expect($status->isNotTested())->toBeTrue();
    });

    test('throws on invalid status', function () {
        MedicalStatus::make('Unknown');
    })->throws(\InvalidArgumentException::class);

    test('can compare statuses', function () {
        $positive1 = MedicalStatus::make('Positive');
        $positive2 = MedicalStatus::make('Positive');
        $negative = MedicalStatus::make('Negative');

        expect($positive1->equals($positive2))->toBeTrue();
        expect($positive1->equals($negative))->toBeFalse();
    });

    test('can convert to spanish string', function () {
        expect((string) MedicalStatus::make('Positive'))->toBe('Positivo');
        expect((string) MedicalStatus::make('Negative'))->toBe('Negativo');
        expect((string) MedicalStatus::make('Not tested'))->toBe('No testeado');
    });

    test('can cast multiple medical statuses', function () {
        $status1 = MedicalStatus::make('Positive');
        $status2 = MedicalStatus::make('Negative');

        expect($status1->isPositive())->toBeTrue();
        expect($status2->isNegative())->toBeTrue();
    });
});
