<?php

namespace App\DTOs;

class PrescriptionItemDTO
{
    public function __construct(
        public readonly string $medication_name,
        public readonly string $dose,
        public readonly ?string $quantity,
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
            medication_name: (string) $data['medication_name'],
            dose: (string) $data['dose'],
            quantity: isset($data['quantity']) && $data['quantity'] !== ''
                ? (string) $data['quantity']
                : null,
            frequency: (string) $data['frequency'],
            duration: (string) $data['duration'],
            instructions: isset($data['instructions']) && $data['instructions'] !== ''
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
            'medication_name' => $this->medication_name,
            'dose' => $this->dose,
            'quantity' => $this->quantity,
            'frequency' => $this->frequency,
            'duration' => $this->duration,
            'instructions' => $this->instructions,
        ];
    }
}
