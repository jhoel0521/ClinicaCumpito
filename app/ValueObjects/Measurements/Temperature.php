<?php

namespace App\ValueObjects\Measurements;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Stringable;

class Temperature implements Castable, Stringable
{
    private float $value;

    private const MIN = 35.0;

    private const MAX = 42.0;

    private function __construct(float $value)
    {
        $this->value = round($value, 2);
        $this->validate();
    }

    private function validate(): void
    {
        if ($this->value < self::MIN) {
            throw new \InvalidArgumentException('La temperatura debe ser al menos '.self::MIN.'°C (Hipotermia)');
        }

        if ($this->value > self::MAX) {
            throw new \InvalidArgumentException('La temperatura no debe exceder '.self::MAX.'°C (Fiebre alta)');
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

    public function isFever(): bool
    {
        return $this->value > 37.5;
    }

    public function isNormal(): bool
    {
        return $this->value >= 36.5 && $this->value <= 37.5;
    }

    public function isHypothermia(): bool
    {
        return $this->value < 36.5;
    }

    public function equals(self $other): bool
    {
        return abs($this->value - $other->value) < 0.01;
    }

    public function __toString(): string
    {
        return "{$this->value}°C";
    }

    public static function castUsing(array $arguments)
    {
        return new class implements \Illuminate\Contracts\Database\Eloquent\CastsAttributes
        {
            public function get($model, $key, $value, $attributes)
            {
                return $value !== null ? Temperature::make($value) : null;
            }

            public function set($model, $key, $value, $attributes)
            {
                if ($value instanceof Temperature) {
                    return $value->value();
                }

                return $value;
            }
        };
    }
}
