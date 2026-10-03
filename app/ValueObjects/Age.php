<?php

namespace App\ValueObjects;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Stringable;

class Age implements Stringable
{
    private function __construct(private CarbonImmutable $birthDate, private CarbonImmutable $referenceDate)
    {
        if ($birthDate->isAfter($referenceDate)) {
            throw new \InvalidArgumentException('La fecha de nacimiento no puede ser futura.');
        }
    }

    public static function fromDates(CarbonInterface|string $birthDate, CarbonInterface|string|null $referenceDate = null): self
    {
        $birth = CarbonImmutable::parse($birthDate)->startOfDay();
        $reference = $referenceDate
            ? CarbonImmutable::parse($referenceDate)->startOfDay()
            : CarbonImmutable::now()->startOfDay();

        return new self($birth, $reference);
    }

    public function years(): int
    {
        return (int) floor($this->birthDate->diffInYears($this->referenceDate));
    }

    public function months(): int
    {
        return (int) floor($this->birthDate->diffInMonths($this->referenceDate));
    }

    public function weeks(): int
    {
        return intdiv($this->days(), 7);
    }

    public function days(): int
    {
        return (int) floor($this->birthDate->diffInDays($this->referenceDate));
    }

    /**
     * Formato único de edad en todo el sistema (perfil, lista, consulta,
     * feed e impresos), pedido por la doctora:
     *
     * - Menos de 1 mes: días ("15 días").
     * - De 1 a 12 meses: meses y días ("12 meses y 5 días"); la edad se
     *   sigue contando en meses hasta cumplir 1 año y 1 mes.
     * - Desde los 13 meses: años y meses, sin días ("1 año y 1 mes", "4 años").
     */
    public function forDisplay(): string
    {
        $diff = $this->birthDate->diff($this->referenceDate);
        $totalMonths = $diff->y * 12 + $diff->m;

        if ($totalMonths === 0) {
            return $diff->d.' '.($diff->d === 1 ? 'día' : 'días');
        }

        if ($totalMonths <= 12) {
            $months = $totalMonths.' '.($totalMonths === 1 ? 'mes' : 'meses');

            return $diff->d > 0 ? $months.' y '.$diff->d.' '.($diff->d === 1 ? 'día' : 'días') : $months;
        }

        $years = intdiv($totalMonths, 12);
        $months = $totalMonths % 12;
        $text = $years.' '.($years === 1 ? 'año' : 'años');

        return $months > 0 ? $text.' y '.$months.' '.($months === 1 ? 'mes' : 'meses') : $text;
    }

    public function __toString(): string
    {
        return $this->forDisplay();
    }
}
