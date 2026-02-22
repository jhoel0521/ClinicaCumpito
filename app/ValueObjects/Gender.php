<?php

namespace App\ValueObjects;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Stringable;

class Gender implements Castable, Stringable
{
    public const MALE = 'M';

    public const FEMALE = 'F';

    private const VALID_VALUES = [self::MALE, self::FEMALE];

    private function __construct(private string $value)
    {
        if (! in_array($value, self::VALID_VALUES, strict: true)) {
            throw new \InvalidArgumentException('El género debe ser M (Masculino) o F (Femenino)');
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

    public function isMale(): bool
    {
        return $this->value === self::MALE;
    }

    public function isFemale(): bool
    {
        return $this->value === self::FEMALE;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return match ($this->value) {
            self::MALE => 'Masculino',
            self::FEMALE => 'Femenino',
        };
    }

    public static function castUsing(array $arguments)
    {
        return new class implements \Illuminate\Contracts\Database\Eloquent\CastsAttributes
        {
            public function get($model, $key, $value, $attributes)
            {
                return $value ? Gender::make($value) : null;
            }

            public function set($model, $key, $value, $attributes)
            {
                if ($value instanceof Gender) {
                    return $value->value();
                }

                return $value;
            }
        };
    }
}
