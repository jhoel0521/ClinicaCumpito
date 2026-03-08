<?php

namespace App\ValueObjects;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Stringable;

class LicenseNumber implements Castable, Stringable
{
    private function __construct(private string $value)
    {
        $this->validate();
    }

    private function validate(): void
    {
        if (empty($this->value)) {
            throw new \InvalidArgumentException('El número de licencia no puede estar vacío');
        }

        if (strlen($this->value) < 5) {
            throw new \InvalidArgumentException('El número de licencia debe tener al menos 5 caracteres');
        }

        if (strlen($this->value) > 50) {
            throw new \InvalidArgumentException('El número de licencia no debe exceder 50 caracteres');
        }

        if (! preg_match('/^[A-Za-z0-9\-\/]+$/', $this->value)) {
            throw new \InvalidArgumentException('El número de licencia solo puede contener letras, números, guiones y barras');
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

    public function equals(self $other): bool
    {
        return strtoupper($this->value) === strtoupper($other->value);
    }

    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return \Illuminate\Contracts\Database\Eloquent\CastsAttributes<LicenseNumber, string>
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
             * @return LicenseNumber|null
             */
            public function get($model, $key, $value, $attributes)
            {
                return $value ? LicenseNumber::make($value) : null;
            }

            public function set($model, $key, $value, $attributes)
            {
                if ($value instanceof LicenseNumber) {
                    return $value->value();
                }

                return $value;
            }
        };
    }
}
