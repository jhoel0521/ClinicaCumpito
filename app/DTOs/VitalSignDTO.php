<?php

namespace App\DTOs;

class VitalSignDTO
{
    public function __construct(
        public readonly ?float $weight,
        public readonly ?float $height,
        public readonly ?float $head_circumference,
        public readonly ?float $temperature,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            weight: isset($data['weight']) ? (float) $data['weight'] : null,
            height: isset($data['height']) ? (float) $data['height'] : null,
            head_circumference: isset($data['head_circumference']) ? (float) $data['head_circumference'] : null,
            temperature: isset($data['temperature']) ? (float) $data['temperature'] : null,
        );
    }

    /**
     * @return array<string, float|null>
     */
    public function toArray(): array
    {
        return [
            'weight' => $this->weight,
            'height' => $this->height,
            'head_circumference' => $this->head_circumference,
            'temperature' => $this->temperature,
        ];
    }
}
