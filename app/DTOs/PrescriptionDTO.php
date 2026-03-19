<?php

namespace App\DTOs;

class PrescriptionDTO
{
    public function __construct(
        public readonly ?string $reason,
        public readonly ?string $observations,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            reason: isset($data['reason']) ? (string) $data['reason'] : null,
            observations: isset($data['observations']) ? (string) $data['observations'] : null,
        );
    }

    /**
     * @return array<string, string|null>
     */
    public function toArray(): array
    {
        return [
            'reason' => $this->reason,
            'observations' => $this->observations,
        ];
    }
}
