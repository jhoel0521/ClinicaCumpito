<?php

namespace App\DTOs;

class PatientVaccineDTO
{
    public function __construct(
        public readonly string $vaccine_id,
        public readonly string $applied_at,
        public readonly ?string $applied_by_doctor_id,
        public readonly ?string $application_site,
        public readonly ?int $dose_number,
        public readonly ?string $notes,
        public readonly bool $applied_elsewhere = false,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            vaccine_id: (string) $data['vaccine_id'],
            applied_at: (string) $data['applied_at'],
            applied_by_doctor_id: isset($data['applied_by_doctor_id']) ? (string) $data['applied_by_doctor_id'] : null,
            application_site: isset($data['application_site']) ? (string) $data['application_site'] : null,
            dose_number: isset($data['dose_number']) ? (int) $data['dose_number'] : null,
            notes: isset($data['notes']) ? (string) $data['notes'] : null,
            applied_elsewhere: isset($data['applied_elsewhere']) ? (bool) $data['applied_elsewhere'] : false,
        );
    }

    /**
     * @return array<string, bool|int|string|null>
     */
    public function toArray(): array
    {
        return [
            'vaccine_id' => $this->vaccine_id,
            'applied_at' => $this->applied_at,
            'applied_by_doctor_id' => $this->applied_by_doctor_id,
            'application_site' => $this->application_site,
            'dose_number' => $this->dose_number,
            'notes' => $this->notes,
            'applied_elsewhere' => $this->applied_elsewhere,
        ];
    }
}
