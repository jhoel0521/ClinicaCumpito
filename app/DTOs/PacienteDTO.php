<?php

namespace App\DTOs;

use App\ValueObjects\BirthType;
use App\ValueObjects\BloodGroup;
use App\ValueObjects\Gender;
use Carbon\Carbon;

class PacienteDTO
{
    public function __construct(
        public readonly ?string $id = null,
        public readonly ?string $user_id = null,
        public readonly ?string $responsible_doctor_id = null,
        public readonly ?string $full_name = null,
        public readonly ?Carbon $date_of_birth = null,
        public readonly Gender|string|null $gender = null,
        public readonly BirthType|string|null $birth_type = null,
        public readonly BloodGroup|string|null $blood_group = null,
        public readonly ?float $birth_weight = null,
        public readonly ?float $birth_height = null,
        public readonly ?float $birth_head_circumference = null,
        public readonly ?bool $heel_prick_done = null,
        public readonly ?string $birth_place = null,
        public readonly ?string $medical_history = null,
        public readonly ?string $allergies = null,
        public readonly ?string $pathologies = null,
        public readonly ?string $surgeries = null,
        /** @var array<int, array{condition_id: string, status?: string, notes?: string}>|null */
        public readonly ?array $medical_conditions = null, // Array de condiciones médicas con status

    ) {}

    /**
     * Crear DTO desde array
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            user_id: $data['user_id'] ?? null,
            responsible_doctor_id: $data['responsible_doctor_id'] ?? null,
            full_name: $data['full_name'] ?? null,
            date_of_birth: isset($data['date_of_birth']) ? Carbon::parse($data['date_of_birth']) : null,
            gender: $data['gender'] ?? null,
            birth_type: $data['birth_type'] ?? null,
            blood_group: $data['blood_group'] ?? null,
            birth_weight: isset($data['birth_weight']) ? (float) $data['birth_weight'] : null,
            birth_height: isset($data['birth_height']) ? (float) $data['birth_height'] : null,
            birth_head_circumference: isset($data['birth_head_circumference']) ? (float) $data['birth_head_circumference'] : null,
            heel_prick_done: isset($data['heel_prick_done']) ? (bool) $data['heel_prick_done'] : null,
            birth_place: $data['birth_place'] ?? null,
            medical_history: $data['medical_history'] ?? null,
            allergies: $data['allergies'] ?? null,
            pathologies: $data['pathologies'] ?? null,
            surgeries: $data['surgeries'] ?? null,
            medical_conditions: $data['medical_conditions'] ?? null,
        );
    }

    /**
     * Convertir DTO a array
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'responsible_doctor_id' => $this->responsible_doctor_id,
            'full_name' => $this->full_name,
            'date_of_birth' => $this->date_of_birth?->toDateString(),
            'gender' => $this->gender instanceof Gender ? $this->gender->value() : $this->gender,
            'birth_type' => $this->birth_type instanceof BirthType ? $this->birth_type->value() : $this->birth_type,
            'blood_group' => $this->blood_group instanceof BloodGroup ? $this->blood_group->value() : $this->blood_group,
            'birth_weight' => $this->birth_weight,
            'birth_height' => $this->birth_height,
            'birth_head_circumference' => $this->birth_head_circumference,
            'heel_prick_done' => $this->heel_prick_done,
            'birth_place' => $this->birth_place,
            'medical_history' => $this->medical_history,
            'allergies' => $this->allergies,
            'pathologies' => $this->pathologies,
            'surgeries' => $this->surgeries,
        ];
    }
}
