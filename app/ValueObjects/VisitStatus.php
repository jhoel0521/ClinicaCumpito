<?php

namespace App\ValueObjects;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Stringable;

/**
 * Cumplimiento de una visita programada, calculado contra las consultas.
 *
 * - A tiempo: hubo consulta entre 3 días antes y 7 días después de la fecha.
 * - En el mes: vino más tarde, pero dentro de los 30 días siguientes.
 * - No vino: pasaron 30 días sin consulta.
 * - Pendiente: todavía no pasó la fecha o sigue dentro del margen.
 */
class VisitStatus implements Stringable
{
    public const PENDING = 'pending';

    public const ON_TIME = 'on_time';

    public const LATE = 'late';

    public const MISSED = 'missed';

    public const DAYS_BEFORE = 3;

    public const ON_TIME_DAYS_AFTER = 7;

    public const LATE_DAYS_AFTER = 30;

    private function __construct(
        private string $value,
        private ?CarbonImmutable $attendedOn = null,
    ) {}

    /**
     * @param  iterable<CarbonInterface>  $consultationDates  fechas de consulta del paciente
     */
    public static function evaluate(CarbonInterface $scheduledFor, iterable $consultationDates, CarbonInterface $today): self
    {
        $scheduled = CarbonImmutable::parse($scheduledFor->format('Y-m-d'));
        $windowStart = $scheduled->subDays(self::DAYS_BEFORE);
        $lateEnd = $scheduled->addDays(self::LATE_DAYS_AFTER);

        $attended = null;
        foreach ($consultationDates as $date) {
            $day = CarbonImmutable::parse($date->format('Y-m-d'));
            if ($day->betweenIncluded($windowStart, $lateEnd) && ($attended === null || $day->lt($attended))) {
                $attended = $day;
            }
        }

        if ($attended !== null) {
            $onTime = $attended->lte($scheduled->addDays(self::ON_TIME_DAYS_AFTER));

            return new self($onTime ? self::ON_TIME : self::LATE, $attended);
        }

        $todayDay = CarbonImmutable::parse($today->format('Y-m-d'));

        return new self($todayDay->gt($lateEnd) ? self::MISSED : self::PENDING);
    }

    /**
     * Conteo por estado para el card de cumplimiento.
     *
     * @param  iterable<self>  $statuses
     * @return array{on_time: int, late: int, missed: int, pending: int}
     */
    public static function summarize(iterable $statuses): array
    {
        $summary = ['on_time' => 0, 'late' => 0, 'missed' => 0, 'pending' => 0];

        foreach ($statuses as $status) {
            match ($status->value) {
                self::ON_TIME => $summary['on_time']++,
                self::LATE => $summary['late']++,
                self::MISSED => $summary['missed']++,
                default => $summary['pending']++,
            };
        }

        return $summary;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function attendedOn(): ?CarbonImmutable
    {
        return $this->attendedOn;
    }

    public function isPending(): bool
    {
        return $this->value === self::PENDING;
    }

    public function label(): string
    {
        return match ($this->value) {
            self::ON_TIME => 'A tiempo',
            self::LATE => 'En el mes',
            self::MISSED => 'No vino',
            default => 'Pendiente',
        };
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
