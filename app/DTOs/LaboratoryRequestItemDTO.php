<?php

namespace App\DTOs;

class LaboratoryRequestItemDTO
{
    public function __construct(
        public readonly string $exam_name,
        public readonly ?string $indications,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            exam_name: (string) $data['exam_name'],
            indications: isset($data['indications'])
                ? (string) $data['indications']
                : null,
        );
    }

    /**
     * @return array<string, string|null>
     */
    public function toArray(): array
    {
        return [
            'exam_name' => $this->exam_name,
            'indications' => $this->indications,
        ];
    }
}
