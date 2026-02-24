<?php

namespace App\DTOs\Catalogs;

class LaboratoryCategoryDTO
{
    public function __construct(
        public readonly ?string $id,
        public readonly string $name,
        public readonly ?string $description = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            name: $data['name'],
            description: $data['description'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
        ], fn ($value) => ! is_null($value));
    }
}
