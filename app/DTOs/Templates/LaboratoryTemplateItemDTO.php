<?php

namespace App\DTOs\Templates;

class LaboratoryTemplateItemDTO
{
    public function __construct(
        public readonly ?string $id,
        public readonly string $laboratory_exam_id,
        public readonly ?string $indications,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            laboratory_exam_id: $data['laboratory_exam_id'],
            indications: $data['indications'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'laboratory_exam_id' => $this->laboratory_exam_id,
            'indications' => $this->indications,
        ], fn ($value) => ! is_null($value));
    }
}
