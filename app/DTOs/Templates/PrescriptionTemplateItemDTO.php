<?php

namespace App\DTOs\Templates;

class PrescriptionTemplateItemDTO
{
    public function __construct(
        public readonly ?string $id,
        public readonly ?string $custom_medication_name,
        public readonly ?string $dose,
        public readonly ?string $frequency,
        public readonly ?string $duration,
        public readonly ?string $instructions,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            custom_medication_name: $data['custom_medication_name'] ?? null,
            dose: $data['dose'] ?? null,
            frequency: $data['frequency'] ?? null,
            duration: $data['duration'] ?? null,
            instructions: $data['instructions'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'custom_medication_name' => $this->custom_medication_name,
            'dose' => $this->dose,
            'frequency' => $this->frequency,
            'duration' => $this->duration,
            'instructions' => $this->instructions,
        ], fn ($value) => ! is_null($value));
    }
}
