<?php

namespace App\DTOs;

class ScheduledVisitDTO
{
    public function __construct(
        public readonly string $scheduled_for,
        public readonly string $reason,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            scheduled_for: (string) $data['scheduled_for'],
            reason: trim((string) $data['reason']),
        );
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'scheduled_for' => $this->scheduled_for,
            'reason' => $this->reason,
        ];
    }
}
