<?php

namespace App\DTOs;

class PrescriptionDTO
{
    public function __construct(
        public readonly ?string $observations,
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
        );
    }

    /**
     * @return array<string, string|null>
     */
    public function toArray(): array
    {
        return [
            'observations' => $this->observations,
        ];
    }
}
