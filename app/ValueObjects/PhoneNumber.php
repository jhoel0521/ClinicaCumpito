<?php

namespace App\ValueObjects;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Stringable;

class PhoneNumber implements Castable, Stringable
{
    private function __construct(private string $value)
    {
        $this->validate();
    }

    private function validate(): void
    {
        if (empty($this->value)) {
            throw new \InvalidArgumentException('El número de teléfono no puede estar vacío');
        }

        // Accept formats: +1234567890, (123) 456-7890, 123-456-7890, 1234567890, +1 (555) 123-4567
        if (! preg_match('/^[\+]?[\s\(\)0-9\-\.]+$/', $this->value)) {
            throw new \InvalidArgumentException('El formato del número de teléfono es inválido');
        }

        $digitsOnly = preg_replace('/\D/', '', $this->value) ?? '';
        if (strlen($digitsOnly) < 7) {
            throw new \InvalidArgumentException('El número de teléfono debe contener al menos 7 dígitos');
        }

        if (strlen($digitsOnly) > 20) {
            throw new \InvalidArgumentException('El número de teléfono no debe exceder 20 dígitos');
        }
    }

    public static function make(string $value): self
    {
        return new self(trim($value));
    }

    public function value(): string
    {
        return $this->value;
    }

    public function digitsOnly(): string
    {
        return preg_replace('/\D/', '', $this->value) ?? '';
    }

    public function equals(self $other): bool
    {
        return $this->digitsOnly() === $other->digitsOnly();
    }

    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return \Illuminate\Contracts\Database\Eloquent\CastsAttributes<PhoneNumber, string>
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
             * @return PhoneNumber|null
             */
            public function get($model, $key, $value, $attributes)
            {
                return $value ? PhoneNumber::make($value) : null;
            }

            public function set($model, $key, $value, $attributes)
            {
                if ($value instanceof PhoneNumber) {
                    return $value->value();
                }

                return $value;
            }
        };
    }
}
