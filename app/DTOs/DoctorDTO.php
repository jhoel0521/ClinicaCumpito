<?php

namespace App\DTOs;

class DoctorDTO
{
    public function __construct(
        public readonly ?string $id = null,
        public readonly ?string $user_id = null,
        public readonly ?string $full_name = null,
        public readonly ?string $specialty = null,
        public readonly ?string $license_number = null,
        public readonly ?bool $active = null,
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
            full_name: $data['full_name'] ?? null,
            specialty: $data['specialty'] ?? null,
            license_number: $data['license_number'] ?? null,
            active: isset($data['active']) ? (bool) $data['active'] : null,
        );
    }

    /**
     * Convertir DTO a array para persistencia
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'full_name' => $this->full_name,
            'specialty' => $this->specialty,
            'license_number' => $this->license_number,
            'active' => $this->active,
        ], fn ($value) => ! is_null($value));
    }
}
