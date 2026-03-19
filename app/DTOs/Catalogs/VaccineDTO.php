<?php

namespace App\DTOs\Catalogs;

class VaccineDTO
{
    public function __construct(
        public readonly ?string $id,
        public readonly string $name,
        public readonly ?string $disease_prevented = null,
        public readonly ?string $recommended_age = null,
        public readonly ?int $dose_sequence = null,
        public readonly ?int $min_age_months = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            name: $data['name'],
            disease_prevented: $data['disease_prevented'] ?? null,
            recommended_age: $data['recommended_age'] ?? null,
            dose_sequence: isset($data['dose_sequence']) ? (int) $data['dose_sequence'] : null,
            min_age_months: isset($data['min_age_months']) ? (int) $data['min_age_months'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'name' => $this->name,
            'disease_prevented' => $this->disease_prevented,
            'recommended_age' => $this->recommended_age,
            'dose_sequence' => $this->dose_sequence,
            'min_age_months' => $this->min_age_months,
        ], fn ($value) => ! is_null($value));
    }
}
