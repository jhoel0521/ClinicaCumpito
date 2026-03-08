<?php

namespace App\ValueObjects\Measurements;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Stringable;

class Weight implements Castable, Stringable
{
    private float $value;

    private const MIN = 0.1;

    private const MAX = 300.0;

    private function __construct(float $value)
    {
        $this->value = round($value, 2);
        $this->validate();
    }

    private function validate(): void
    {
        if ($this->value < self::MIN) {
            throw new \InvalidArgumentException('El peso debe ser al menos '.self::MIN.' kg');
        }

        if ($this->value > self::MAX) {
            throw new \InvalidArgumentException('El peso no debe exceder '.self::MAX.' kg');
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

    public function inGrams(): float
    {
        return round($this->value * 1000, 2);
    }

    public function inPounds(): float
    {
        return round($this->value * 2.20462, 2);
    }

    public function equals(self $other): bool
    {
        return abs($this->value - $other->value) < 0.01;
    }

    public function __toString(): string
    {
        return "{$this->value} kg";
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return \Illuminate\Contracts\Database\Eloquent\CastsAttributes<Weight, float|int|string>
     */
    public static function castUsing(array $arguments)
    {
        return new class implements \Illuminate\Contracts\Database\Eloquent\CastsAttributes
        {
            /**
             * @param  \Illuminate\Database\Eloquent\Model  $model
             * @param  string  $key
             * @param  float|int|string|null  $value
             * @param  array<string, mixed>  $attributes
             * @return Weight|null
             */
            public function get($model, $key, $value, $attributes)
            {
                return $value !== null ? Weight::make($value) : null;
            }

            public function set($model, $key, $value, $attributes)
            {
                if ($value instanceof Weight) {
                    return $value->value();
                }

                return $value;
            }
        };
    }
}
