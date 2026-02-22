<?php

namespace App\ValueObjects;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Stringable;

class BloodGroup implements Castable, Stringable
{
    public const O_POSITIVE = 'O+';

    public const O_NEGATIVE = 'O-';

    public const A_POSITIVE = 'A+';

    public const A_NEGATIVE = 'A-';

    public const B_POSITIVE = 'B+';

    public const B_NEGATIVE = 'B-';

    public const AB_POSITIVE = 'AB+';

    public const AB_NEGATIVE = 'AB-';

    private const VALID_VALUES = [
        self::O_POSITIVE,
        self::O_NEGATIVE,
        self::A_POSITIVE,
        self::A_NEGATIVE,
        self::B_POSITIVE,
        self::B_NEGATIVE,
        self::AB_POSITIVE,
        self::AB_NEGATIVE,
    ];

    private function __construct(private string $value)
    {
        if (! in_array($value, self::VALID_VALUES, strict: true)) {
            throw new \InvalidArgumentException(
                'El grupo sanguíneo debe ser uno de: '.implode(', ', self::VALID_VALUES)
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

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public static function castUsing(array $arguments)
    {
        return new class implements \Illuminate\Contracts\Database\Eloquent\CastsAttributes
        {
            public function get($model, $key, $value, $attributes)
            {
                return $value ? BloodGroup::make($value) : null;
            }

            public function set($model, $key, $value, $attributes)
            {
                if ($value instanceof BloodGroup) {
                    return $value->value();
                }

                return $value;
            }
        };
    }
}
