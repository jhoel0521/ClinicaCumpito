<?php

namespace App\DTOs\Catalogs;

class LaboratoryExamDTO
{
    public function __construct(
        public readonly ?string $id,
        public readonly string $category_id,
        public readonly string $name,
        public readonly ?string $description = null,
        public readonly ?string $unit = null,
        public readonly ?string $reference_range = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            category_id: $data['category_id'],
            name: $data['name'],
            description: $data['description'] ?? null,
            unit: $data['unit'] ?? null,
            reference_range: $data['reference_range'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'category_id' => $this->category_id,
            'name' => $this->name,
            'description' => $this->description,
            'unit' => $this->unit,
            'reference_range' => $this->reference_range,
        ], fn ($value) => ! is_null($value));
    }
}
