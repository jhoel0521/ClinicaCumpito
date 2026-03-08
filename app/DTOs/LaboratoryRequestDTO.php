<?php

namespace App\DTOs;

class LaboratoryRequestDTO
{
    public function __construct(
        public readonly ?string $source_template_id,
        public readonly ?string $observations,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            source_template_id: isset($data['source_template_id'])
                ? (string) $data['source_template_id']
                : null,
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
            'source_template_id' => $this->source_template_id,
            'observations' => $this->observations,
        ];
    }
}
