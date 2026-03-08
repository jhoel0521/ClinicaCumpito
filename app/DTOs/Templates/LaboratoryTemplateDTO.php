<?php

namespace App\DTOs\Templates;

class LaboratoryTemplateDTO
{
    /**
     * @param  array<int, LaboratoryTemplateItemDTO>  $items
     */
    public function __construct(
        public readonly ?string $id,
        public readonly string $doctor_id,
        public readonly string $name,
        public readonly ?string $description,
        public readonly bool $is_active = true,
        public readonly array $items = [],
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $items = array_map(
            fn ($item) => LaboratoryTemplateItemDTO::fromArray($item),
            $data['items'] ?? []
        );

        return new self(
            id: $data['id'] ?? null,
            doctor_id: $data['doctor_id'],
            name: $data['name'],
            description: $data['description'] ?? null,
            is_active: $data['is_active'] ?? true,
            items: $items,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'doctor_id' => $this->doctor_id,
            'name' => $this->name,
            'description' => $this->description,
            'is_active' => $this->is_active,
        ], fn ($value) => ! is_null($value));
    }
}
