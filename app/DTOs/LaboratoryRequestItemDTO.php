<?php

namespace App\DTOs;

class LaboratoryRequestItemDTO
{
    public function __construct(
        public readonly string $exam_name,
        public readonly ?string $parameter_name = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            exam_name: (string) $data['exam_name'],
            parameter_name: isset($data['parameter_name']) ? (string) $data['parameter_name'] : null,
        );
    }

    /**
     * @return array<string, string|null>
     */
    public function toArray(): array
    {
        return [
            'exam_name' => $this->exam_name,
            'parameter_name' => $this->parameter_name,
        ];
    }
}
