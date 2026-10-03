<?php

use App\ValueObjects\Age;
use Carbon\Carbon;

describe('Age', function () {
    test('calcula días, semanas, meses y años', function () {
        Carbon::setTestNow('2026-02-26');

        expect(Age::fromDates('2026-02-20')->days())->toBe(6)
            ->and(Age::fromDates('2026-01-15')->weeks())->toBe(6)
            ->and(Age::fromDates('2025-06-10')->months())->toBe(8)
            ->and(Age::fromDates('2020-01-10')->years())->toBe(6);
    });

    test('lanza excepcion si la fecha de nacimiento es futura', function () {
        Carbon::setTestNow('2026-02-26');

        Age::fromDates('2026-03-10');
    })->throws(\InvalidArgumentException::class);

    test('menos de un mes se muestra en días', function () {
        Carbon::setTestNow('2026-02-26');

        expect(Age::fromDates('2026-02-20')->forDisplay())->toBe('6 días')
            ->and(Age::fromDates('2026-02-25')->forDisplay())->toBe('1 día')
            ->and(Age::fromDates('2026-02-26')->forDisplay())->toBe('0 días');
    });

    test('de 1 a 12 meses se muestra en meses y días', function () {
        Carbon::setTestNow('2026-08-07');

        expect(Age::fromDates('2026-07-07')->forDisplay())->toBe('1 mes')
            ->and(Age::fromDates('2026-07-06')->forDisplay())->toBe('1 mes y 1 día')
            ->and(Age::fromDates('2026-01-01')->forDisplay())->toBe('7 meses y 6 días')
            ->and(Age::fromDates('2026-01-15')->forDisplay())->toBe('6 meses y 23 días');
    });

    test('hasta cumplir 13 meses sigue en meses: 12 meses y días', function () {
        Carbon::setTestNow('2026-01-07');

        expect(Age::fromDates('2025-01-07')->forDisplay())->toBe('12 meses')
            ->and(Age::fromDates('2024-12-17')->forDisplay())->toBe('12 meses y 21 días');
    });

    test('desde los 13 meses pasa a años y meses, sin días', function () {
        Carbon::setTestNow('2026-08-02');

        expect(Age::fromDates('2025-07-02')->forDisplay())->toBe('1 año y 1 mes')
            ->and(Age::fromDates('2025-06-20')->forDisplay())->toBe('1 año y 1 mes')
            ->and(Age::fromDates('2024-04-17')->forDisplay())->toBe('2 años y 3 meses')
            ->and(Age::fromDates('2024-08-02')->forDisplay())->toBe('2 años')
            ->and(Age::fromDates('2022-10-03')->forDisplay())->toBe('3 años y 9 meses');
    });

    test('el texto de la edad es el mismo formato', function () {
        Carbon::setTestNow('2026-02-26');

        expect((string) Age::fromDates('2020-01-10'))->toBe('6 años y 1 mes');
    });
});
