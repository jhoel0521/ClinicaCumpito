<?php

namespace App\ValueObjects;

use Stringable;

class ZScore implements Stringable
{
    private function __construct(private float $value)
    {
        if (! is_finite($value)) {
            throw new \InvalidArgumentException('El Z-Score debe ser un número finito.');
        }
    }

    public static function make(float|int|string $value): self
    {
        return new self((float) $value);
    }

    public function value(): float
    {
        return $this->value;
    }

    public function rounded(int $precision = 2): float
    {
        return round($this->value, $precision);
    }

    public function isNormalRange(): bool
    {
        return $this->value >= -2.0 && $this->value <= 2.0;
    }

    public function category(): string
    {
        if ($this->value < -3.0) {
            return 'Severamente bajo';
        }

        if ($this->value < -2.0) {
            return 'Bajo';
        }

        if ($this->value > 3.0) {
            return 'Severamente alto';
        }

        if ($this->value > 2.0) {
            return 'Alto';
        }

        return 'Normal';
    }

    public function __toString(): string
    {
        return (string) $this->rounded(2);
    }
}
