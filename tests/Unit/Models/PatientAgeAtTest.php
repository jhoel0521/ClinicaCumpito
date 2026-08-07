<?php

use App\Models\Patient;
use App\ValueObjects\Age;
use Carbon\Carbon;

beforeEach(function (): void {
    Carbon::setTestNow('2026-08-07 10:00:00');
});

afterEach(function (): void {
    Carbon::setTestNow();
});

test('ageAt calcula la edad del paciente en una fecha de referencia', function (): void {
    $patient = Patient::factory()->create([
        'date_of_birth' => '2026-01-07',
    ]);

    $age = $patient->ageAt('2026-08-07');

    expect($age)->toBeInstanceOf(Age::class)
        ->and($age->months())->toBe(7)
        ->and($age->forDisplayFull())->toBe('7 meses');
});

test('ageAt devuelve la edad en una consulta pasada distinta a la actual', function (): void {
    $patient = Patient::factory()->create([
        'date_of_birth' => '2026-01-07',
    ]);

    // El 7 de julio tenía 6 meses; hoy (7 de agosto) tiene 7
    expect($patient->ageAt('2026-07-07')->months())->toBe(6)
        ->and($patient->age()->months())->toBe(7);
});

test('ageAt usa la fecha actual si no se pasa referencia', function (): void {
    $patient = Patient::factory()->create([
        'date_of_birth' => '2026-01-07',
    ]);

    expect($patient->ageAt(null)->months())->toBe(7);
});

test('ageAt devuelve null si el paciente no tiene fecha de nacimiento', function (): void {
    $patient = Patient::factory()->create([
        'date_of_birth' => null,
    ]);

    expect($patient->ageAt('2026-08-07'))->toBeNull();
});
