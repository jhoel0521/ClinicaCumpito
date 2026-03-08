<?php

namespace App\ValueObjects\Measurements;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Stringable;

class HeadCircumference implements Castable, Stringable
{
    private float $value;

    private const MIN = 20.0;

    private const MAX = 80.0;

    private function __construct(float $value)
    {
        $this->value = round($value, 2);
        $this->validate();
    }

    private function validate(): void
    {
        if ($this->value < self::MIN) {
            throw new \InvalidArgumentException('El perímetro cefálico debe ser al menos '.self::MIN.' cm');
        }

        if ($this->value > self::MAX) {
            throw new \InvalidArgumentException('El perímetro cefálico no debe exceder '.self::MAX.' cm');
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

    /**
     * @param  array<string, mixed>  $arguments
     * @return \Illuminate\Contracts\Database\Eloquent\CastsAttributes<HeadCircumference, float|int|string>
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
             * @return HeadCircumference|null
             */
            public function get($model, $key, $value, $attributes)
            {
                return $value !== null ? HeadCircumference::make($value) : null;
            }

            public function set($model, $key, $value, $attributes)
            {
                if ($value instanceof HeadCircumference) {
                    return $value->value();
                }

                return $value;
            }
        };
    }
}
