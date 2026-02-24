<?php

namespace App\DTOs\Catalogs;

class MedicationDTO
{
    public function __construct(
        public readonly ?string $id,
        public readonly string $name,
        public readonly ?string $generic_name = null,
        public readonly ?string $pharmaceutical_form = null,
        public readonly ?string $concentration = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            name: $data['name'],
            generic_name: $data['generic_name'] ?? null,
            pharmaceutical_form: $data['pharmaceutical_form'] ?? null,
            concentration: $data['concentration'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'name' => $this->name,
            'generic_name' => $this->generic_name,
            'pharmaceutical_form' => $this->pharmaceutical_form,
            'concentration' => $this->concentration,
        ], fn ($value) => ! is_null($value));
    }
}
