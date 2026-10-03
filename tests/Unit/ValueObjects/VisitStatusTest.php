<?php

use App\ValueObjects\VisitStatus;
use Carbon\CarbonImmutable;

/*
 * Reglas acordadas con la doctora para una visita programada:
 * a tiempo = consulta entre 3 días antes y 7 después; en el mes = hasta 30
 * días después; no vino = pasaron 30 días sin consulta; si no, pendiente.
 */

function evaluate(string $scheduled, array $consultations, string $today): VisitStatus
{
    return VisitStatus::evaluate(
        CarbonImmutable::parse($scheduled),
        array_map(fn ($d) => CarbonImmutable::parse($d), $consultations),
        CarbonImmutable::parse($today),
    );
}

test('consulta el mismo día o dentro del margen cuenta como a tiempo', function (string $consultation): void {
    $status = evaluate('2026-10-10', [$consultation], '2026-11-30');

    expect($status->value())->toBe(VisitStatus::ON_TIME)
        ->and($status->label())->toBe('A tiempo')
        ->and($status->attendedOn()->format('Y-m-d'))->toBe($consultation);
})->with(['2026-10-07', '2026-10-10', '2026-10-17']);

test('consulta entre el día 8 y el 30 después cuenta como en el mes', function (string $consultation): void {
    expect(evaluate('2026-10-10', [$consultation], '2026-12-31')->value())->toBe(VisitStatus::LATE);
})->with(['2026-10-18', '2026-11-09']);

test('sin consulta y pasados 30 días es no vino', function (): void {
    expect(evaluate('2026-10-10', ['2026-10-06', '2026-11-10'], '2026-11-10')->value())->toBe(VisitStatus::MISSED);
});

test('sin consulta y dentro del margen sigue pendiente', function (string $today): void {
    $status = evaluate('2026-10-10', [], $today);

    expect($status->isPending())->toBeTrue()
        ->and($status->attendedOn())->toBeNull();
})->with(['2026-10-01', '2026-10-10', '2026-11-09']);

test('si hay varias consultas en la ventana se toma la primera', function (): void {
    $status = evaluate('2026-10-10', ['2026-10-25', '2026-10-12'], '2026-12-01');

    expect($status->value())->toBe(VisitStatus::ON_TIME)
        ->and($status->attendedOn()->format('Y-m-d'))->toBe('2026-10-12');
});

test('la consulta en la que se programó la visita no la cumple', function (): void {
    // En la consulta del 03/10 a las 10:00 la doctora programa "vuelva el 07/10".
    $status = VisitStatus::evaluate(
        CarbonImmutable::parse('2026-10-07'),
        [CarbonImmutable::parse('2026-10-03 10:00:00')],
        CarbonImmutable::parse('2026-10-03'),
        CarbonImmutable::parse('2026-10-03 10:20:00'),
    );

    expect($status->isPending())->toBeTrue();
});

test('una consulta posterior a la programación sí la cumple aunque sea antes de la fecha', function (): void {
    $status = VisitStatus::evaluate(
        CarbonImmutable::parse('2026-10-07'),
        [CarbonImmutable::parse('2026-10-03 10:00:00'), CarbonImmutable::parse('2026-10-05 09:00:00')],
        CarbonImmutable::parse('2026-10-05'),
        CarbonImmutable::parse('2026-10-03 10:20:00'),
    );

    expect($status->value())->toBe(VisitStatus::ON_TIME)
        ->and($status->attendedOn()->format('Y-m-d'))->toBe('2026-10-05');
});

test('la hora de la consulta no afecta el cálculo', function (): void {
    expect(evaluate('2026-10-10', ['2026-10-17 23:59:00'], '2026-12-01')->value())->toBe(VisitStatus::ON_TIME);
});
