<?php

namespace App\ValueObjects\Measurements;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Stringable;

class Height implements Castable, Stringable
{
    private float $value;

    private const MIN = 0.1;

    private const MAX = 250.0;

    private function __construct(float $value)
    {
        $this->value = round($value, 2);
        $this->validate();
    }

    private function validate(): void
    {
        if ($this->value < self::MIN) {
            throw new \InvalidArgumentException('La altura debe ser al menos '.self::MIN.' cm');
        }

        if ($this->value > self::MAX) {
            throw new \InvalidArgumentException('La altura no debe exceder '.self::MAX.' cm');
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

    public function inMeters(): float
    {
        return round($this->value / 100, 2);
    }

    public function inInches(): float
    {
        return round($this->value / 2.54, 2);
    }

    public function equals(self $other): bool
    {
        return abs($this->value - $other->value) < 0.01;
    }

    public function __toString(): string
    {
        return "{$this->value} cm";
    }

    public static function castUsing(array $arguments)
    {
        return new class implements \Illuminate\Contracts\Database\Eloquent\CastsAttributes
        {
            public function get($model, $key, $value, $attributes)
            {
                return $value !== null ? Height::make($value) : null;
            }

            public function set($model, $key, $value, $attributes)
            {
                if ($value instanceof Height) {
                    return $value->value();
                }

                return $value;
            }
        };
    }
}
