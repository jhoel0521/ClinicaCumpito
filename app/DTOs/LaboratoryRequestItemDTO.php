<?php

namespace App\DTOs;

class LaboratoryRequestItemDTO
{
    public function __construct(
        public readonly string $exam_name,
        public readonly ?string $parameter_name = null,
        public readonly ?string $indications = null,
        public readonly ?string $result_value = null,
        public readonly bool $is_abnormal = false,
        public readonly ?string $result_notes = null,
        public readonly ?string $result_received_at = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            exam_name: (string) $data['exam_name'],
            parameter_name: isset($data['parameter_name']) ? (string) $data['parameter_name'] : null,
            indications: isset($data['indications']) ? (string) $data['indications'] : null,
            result_value: isset($data['result_value']) ? (string) $data['result_value'] : null,
            is_abnormal: ! empty($data['is_abnormal']),
            result_notes: isset($data['result_notes']) ? (string) $data['result_notes'] : null,
            result_received_at: isset($data['result_received_at']) ? (string) $data['result_received_at'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'exam_name' => $this->exam_name,
            'parameter_name' => $this->parameter_name,
            'indications' => $this->indications,
            'result_value' => $this->result_value,
            'is_abnormal' => $this->is_abnormal,
            'result_notes' => $this->result_notes,
            'result_received_at' => $this->result_received_at,
        ];
    }
}
