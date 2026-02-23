<?php

namespace App\ValueObjects;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Stringable;

class MedicalStatus implements Castable, Stringable
{
    public const POSITIVE = 'Positive';

    public const NEGATIVE = 'Negative';

    public const NOT_TESTED = 'Not tested';

    private const VALID_VALUES = [self::POSITIVE, self::NEGATIVE, self::NOT_TESTED];

    private function __construct(private string $value)
    {
        if (! in_array($value, self::VALID_VALUES, strict: true)) {
            throw new \InvalidArgumentException(
                'El estado médico debe ser uno de: '.implode(', ', self::VALID_VALUES)
            );
        }
    }

    public static function make(string $value): self
    {
        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function isPositive(): bool
    {
        return $this->value === self::POSITIVE;
    }

    public function isNegative(): bool
    {
        return $this->value === self::NEGATIVE;
    }

    public function isNotTested(): bool
    {
        return $this->value === self::NOT_TESTED;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return match ($this->value) {
            self::POSITIVE => 'Positivo',
            self::NEGATIVE => 'Negativo',
            self::NOT_TESTED => 'No testeado',
            default => $this->value,
        };
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return \Illuminate\Contracts\Database\Eloquent\CastsAttributes<MedicalStatus, string>
     */
    public static function castUsing(array $arguments)
    {
        return new class implements \Illuminate\Contracts\Database\Eloquent\CastsAttributes
        {
            /**
             * @param  \Illuminate\Database\Eloquent\Model  $model
             * @param  string  $key
             * @param  string|null  $value
             * @param  array<string, mixed>  $attributes
             * @return MedicalStatus|null
             */
            public function get($model, $key, $value, $attributes)
            {
                return $value ? MedicalStatus::make($value) : null;
            }

            public function set($model, $key, $value, $attributes)
            {
                if ($value instanceof MedicalStatus) {
                    return $value->value();
                }

                return $value;
            }
        };
    }
}
