<?php

namespace App\DTOs;

/**
 * Un medicamento tal como se imprime en la receta.
 */
class MedicationItemDTO
{
    public function __construct(
        public readonly string $medication_name,
        public readonly string $dose,
        public readonly ?string $administration_route,
        public readonly string $frequency,
        public readonly string $duration,
        public readonly ?string $instructions,
    ) {}

    /** @param  array<string, mixed>  $data */
    public static function fromArray(array $data): self
    {
        return new self(
            medication_name: (string) $data['medication_name'],
            dose: (string) $data['dose'],
            administration_route: isset($data['administration_route']) && $data['administration_route'] !== ''
                ? (string) $data['administration_route']
                : null,
            frequency: (string) $data['frequency'],
            duration: (string) $data['duration'],
            instructions: isset($data['instructions']) && $data['instructions'] !== ''
                ? (string) $data['instructions']
                : null,
        );
    }
}
