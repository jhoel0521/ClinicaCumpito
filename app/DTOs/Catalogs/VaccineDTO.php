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
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            name: $data['name'],
            disease_prevented: $data['disease_prevented'] ?? null,
            recommended_age: $data['recommended_age'] ?? null,
            dose_sequence: isset($data['dose_sequence']) ? (int) $data['dose_sequence'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'name' => $this->name,
            'disease_prevented' => $this->disease_prevented,
            'recommended_age' => $this->recommended_age,
            'dose_sequence' => $this->dose_sequence,
        ], fn ($value) => ! is_null($value));
    }
}
