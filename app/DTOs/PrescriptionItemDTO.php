<?php

namespace App\DTOs;

class PrescriptionItemDTO
{
    public function __construct(
        public readonly ?string $source_template_item_id,
        public readonly string $medication_name,
        public readonly string $dose,
        public readonly string $frequency,
        public readonly string $duration,
        public readonly ?string $instructions,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            source_template_item_id: isset($data['source_template_item_id'])
                ? (string) $data['source_template_item_id']
                : null,
            medication_name: (string) $data['medication_name'],
            dose: (string) $data['dose'],
            frequency: (string) $data['frequency'],
            duration: (string) $data['duration'],
            instructions: isset($data['instructions'])
                ? (string) $data['instructions']
                : null,
        );
    }

    /**
     * @return array<string, string|null>
     */
    public function toArray(): array
    {
        return [
            'source_template_item_id' => $this->source_template_item_id,
            'medication_name' => $this->medication_name,
            'dose' => $this->dose,
            'frequency' => $this->frequency,
            'duration' => $this->duration,
            'instructions' => $this->instructions,
        ];
    }
}
