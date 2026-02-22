<?php

namespace App\ValueObjects;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Stringable;

class ConsultationStatus implements Castable, Stringable
{
    public const DRAFT = 'draft';

    public const SAVED = 'saved';

    public const FINALIZED = 'finalized';

    private const VALID_VALUES = [self::DRAFT, self::SAVED, self::FINALIZED];

    private function __construct(private string $value)
    {
        if (! in_array($value, self::VALID_VALUES, strict: true)) {
            throw new \InvalidArgumentException(
                'El estado de la consulta debe ser uno de: '.implode(', ', self::VALID_VALUES)
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

    public function isDraft(): bool
    {
        return $this->value === self::DRAFT;
    }

    public function isSaved(): bool
    {
        return $this->value === self::SAVED;
    }

    public function isFinalized(): bool
    {
        return $this->value === self::FINALIZED;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return match ($this->value) {
            self::DRAFT => 'Borrador',
            self::SAVED => 'Guardada',
            self::FINALIZED => 'Finalizada',
        };
    }

    public static function castUsing(array $arguments)
    {
        return new class implements \Illuminate\Contracts\Database\Eloquent\CastsAttributes
        {
            public function get($model, $key, $value, $attributes)
            {
                return $value ? ConsultationStatus::make($value) : null;
            }

            public function set($model, $key, $value, $attributes)
            {
                if ($value instanceof ConsultationStatus) {
                    return $value->value();
                }

                return $value;
            }
        };
    }
}
