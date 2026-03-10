<?php

namespace App\DTOs;

class LaboratoryRequestDTO
{
    public function __construct(
        public readonly ?string $observations,
        public readonly string $status = 'pending',
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
        ];
    }
}
