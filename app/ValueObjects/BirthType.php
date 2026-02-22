<?php

namespace App\ValueObjects;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Stringable;

class BirthType implements Castable, Stringable
{
    public const NORMAL = 'Normal';

    public const CESAREAN = 'Cesarean';

    private const VALID_VALUES = [self::NORMAL, self::CESAREAN];

    private function __construct(private string $value)
    {
        if (! in_array($value, self::VALID_VALUES, strict: true)) {
            throw new \InvalidArgumentException(
                'El tipo de parto debe ser uno de: '.implode(', ', self::VALID_VALUES)
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

    public function isNormal(): bool
    {
        return $this->value === self::NORMAL;
    }

    public function isCesarean(): bool
    {
        return $this->value === self::CESAREAN;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return match ($this->value) {
            self::NORMAL => 'Parto Natural',
            self::CESAREAN => 'Parto por Cesárea',
        };
    }

    public static function castUsing(array $arguments)
    {
        return new class implements \Illuminate\Contracts\Database\Eloquent\CastsAttributes
        {
            public function get($model, $key, $value, $attributes)
            {
                return $value ? BirthType::make($value) : null;
            }

            public function set($model, $key, $value, $attributes)
            {
                if ($value instanceof BirthType) {
                    return $value->value();
                }

                return $value;
            }
        };
    }
}
