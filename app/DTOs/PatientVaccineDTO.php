<?php

namespace App\DTOs;

class PatientVaccineDTO
{
    public function __construct(
        public readonly string $vaccine_id,
        public readonly string $applied_at,
        public readonly ?int $dose_number,
        public readonly ?string $notes,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            vaccine_id: (string) $data['vaccine_id'],
            applied_at: (string) $data['applied_at'],
            dose_number: isset($data['dose_number']) ? (int) $data['dose_number'] : null,
            notes: isset($data['notes']) ? (string) $data['notes'] : null,
        );
    }

    /**
     * @return array<string, int|string|null>
     */
    public function toArray(): array
    {
        return [
            'vaccine_id' => $this->vaccine_id,
            'applied_at' => $this->applied_at,
            'dose_number' => $this->dose_number,
            'notes' => $this->notes,
        ];
    }
}
