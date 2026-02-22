<?php

namespace App\ValueObjects;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Stringable;

class ConsultationType implements Castable, Stringable
{
    public const DIGITAL = 'digital';

    public const MANUAL = 'manual';

    private const VALID_VALUES = [self::DIGITAL, self::MANUAL];

    private function __construct(private string $value)
    {
        if (! in_array($value, self::VALID_VALUES, strict: true)) {
            throw new \InvalidArgumentException(
                'El tipo de consulta debe ser uno de: '.implode(', ', self::VALID_VALUES)
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

    public function isDigital(): bool
    {
        return $this->value === self::DIGITAL;
    }

    public function isManual(): bool
    {
        return $this->value === self::MANUAL;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return match ($this->value) {
            self::DIGITAL => 'Consulta Digital',
            self::MANUAL => 'Consulta Manual',
        };
    }

    public static function castUsing(array $arguments)
    {
        return new class implements \Illuminate\Contracts\Database\Eloquent\CastsAttributes
        {
            public function get($model, $key, $value, $attributes)
            {
                return $value ? ConsultationType::make($value) : null;
            }

            public function set($model, $key, $value, $attributes)
            {
                if ($value instanceof ConsultationType) {
                    return $value->value();
                }

                return $value;
            }
        };
    }
}
