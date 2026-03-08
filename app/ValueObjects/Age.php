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

    public function forDisplay(): string
    {
        $days = $this->days();

        if ($days < 30) {
            return $days.' '.($days === 1 ? 'día' : 'días');
        }

        if ($days < 90) {
            $weeks = $this->weeks();

            return $weeks.' '.($weeks === 1 ? 'semana' : 'semanas');
        }

        if ($this->years() < 2) {
            $months = $this->months();

            return $months.' '.($months === 1 ? 'mes' : 'meses');
        }

        return $this->years().' años';
    }

    public function __toString(): string
    {
        return $this->forDisplay();
    }
}
