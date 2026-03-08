<?php

use App\ValueObjects\Age;
use Carbon\Carbon;

describe('Age', function () {
    test('calcula edad en dias cuando es menor a un mes', function () {
        Carbon::setTestNow('2026-02-26');

        $age = Age::fromDates('2026-02-20');

        expect($age->days())->toBe(6)
            ->and($age->forDisplay())->toBe('6 días');
    });

    test('calcula edad en semanas cuando es menor a tres meses', function () {
        Carbon::setTestNow('2026-02-26');

        $age = Age::fromDates('2026-01-15');

        expect($age->weeks())->toBe(6)
            ->and($age->forDisplay())->toBe('6 semanas');
    });

    test('calcula edad en meses cuando es menor a dos años', function () {
        Carbon::setTestNow('2026-02-26');

        $age = Age::fromDates('2025-06-10');

        expect($age->months())->toBe(8)
            ->and($age->forDisplay())->toBe('8 meses');
    });

    test('calcula edad en años para mayores de dos años', function () {
        Carbon::setTestNow('2026-02-26');

        $age = Age::fromDates('2020-01-10');

        expect($age->years())->toBe(6)
            ->and($age->forDisplay())->toBe('6 años')
            ->and((string) $age)->toBe('6 años');
    });

    test('lanza excepcion si la fecha de nacimiento es futura', function () {
        Carbon::setTestNow('2026-02-26');

        Age::fromDates('2026-03-10');
    })->throws(\InvalidArgumentException::class);
});
