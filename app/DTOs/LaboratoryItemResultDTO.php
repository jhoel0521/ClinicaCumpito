<?php

namespace App\DTOs;

class LaboratoryItemResultDTO
{
    public function __construct(
        public readonly string $laboratory_request_item_id,
        public readonly ?string $value = null,
        public readonly ?string $report_text = null,
        public readonly bool $is_abnormal = false,
        public readonly int $sort_order = 0,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            laboratory_request_item_id: (string) $data['laboratory_request_item_id'],
            value: isset($data['value']) ? (string) $data['value'] : null,
            report_text: isset($data['report_text']) ? (string) $data['report_text'] : null,
            is_abnormal: ! empty($data['is_abnormal']),
            sort_order: isset($data['sort_order']) ? (int) $data['sort_order'] : 0,
        );
    }

    /**
     * @return array<string, bool|int|string|null>
     */
    public function toArray(): array
    {
        return [
            'laboratory_request_item_id' => $this->laboratory_request_item_id,
            'value' => $this->value,
            'report_text' => $this->report_text,
            'is_abnormal' => $this->is_abnormal,
            'sort_order' => $this->sort_order,
        ];
    }
}
