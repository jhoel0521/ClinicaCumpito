<?php

namespace App\DTOs;

class SoapNoteDTO
{
    public function __construct(
        public readonly ?string $subjective,
        public readonly ?string $objective,
        public readonly ?string $assessment,
        public readonly ?string $plan,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            subjective: isset($data['subjective']) ? (string) $data['subjective'] : null,
            objective: isset($data['objective']) ? (string) $data['objective'] : null,
            assessment: isset($data['assessment']) ? (string) $data['assessment'] : null,
            plan: isset($data['plan']) ? (string) $data['plan'] : null,
        );
    }

    /**
     * @return array<string, string|null>
     */
    public function toArray(): array
    {
        return [
            'subjective' => $this->subjective,
            'objective' => $this->objective,
            'assessment' => $this->assessment,
            'plan' => $this->plan,
        ];
    }
}
