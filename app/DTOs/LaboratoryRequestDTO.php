<?php

namespace App\DTOs;

class LaboratoryRequestDTO
{
    public function __construct(
        public readonly ?string $observations,
        public readonly string $status = 'pending',
        public readonly ?string $presumptive_diagnosis = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            observations: isset($data['observations'])
                ? (string) $data['observations']
                : null,
            status: isset($data['status']) ? (string) $data['status'] : 'pending',
            presumptive_diagnosis: isset($data['presumptive_diagnosis'])
                ? (string) $data['presumptive_diagnosis']
                : null,
        );
    }

    /**
     * @return array<string, string|null>
     */
    public function toArray(): array
    {
        return [
            'observations' => $this->observations,
            'status' => $this->status,
            'presumptive_diagnosis' => $this->presumptive_diagnosis,
        ];
    }
}
