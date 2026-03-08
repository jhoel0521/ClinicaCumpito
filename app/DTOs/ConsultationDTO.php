<?php

namespace App\DTOs;

class ConsultationDTO
{
    public function __construct(
        public readonly string $patient_id,
        public readonly string $doctor_id,
        public readonly string $type,
        public readonly string $status,
        public readonly string $consultation_date,
        public readonly ?string $scanned_file_path = null,
        public readonly bool $pending_transcription = false,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            patient_id: (string) $data['patient_id'],
            doctor_id: (string) $data['doctor_id'],
            type: (string) $data['type'],
            status: (string) ($data['status'] ?? 'saved'),
            consultation_date: (string) $data['consultation_date'],
            scanned_file_path: isset($data['scanned_file_path']) ? (string) $data['scanned_file_path'] : null,
            pending_transcription: (bool) ($data['pending_transcription'] ?? false),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'patient_id' => $this->patient_id,
            'doctor_id' => $this->doctor_id,
            'type' => $this->type,
            'status' => $this->status,
            'consultation_date' => $this->consultation_date,
            'scanned_file_path' => $this->scanned_file_path,
            'pending_transcription' => $this->pending_transcription,
        ];
    }
}
