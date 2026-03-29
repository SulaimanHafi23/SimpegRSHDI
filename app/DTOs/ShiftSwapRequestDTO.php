<?php

namespace App\DTOs;

class ShiftSwapRequestDTO
{
    public function __construct(
        public readonly string $requester_id,
        public readonly string $requester_shift_id,
        public readonly string $swap_type = 'single_date',
        public readonly ?string $id = null,
        public readonly ?string $target_worker_id = null,
        public readonly ?string $target_shift_id = null,
        public readonly ?string $swap_date = null,
        public readonly ?string $swap_start_date = null,
        public readonly ?string $swap_end_date = null,
        public readonly ?array $swap_dates = null,
        public readonly ?string $reason = null,
        public readonly ?array $metadata = null,
    ) {}

    public static function fromRequest(array $data): self
    {
        $swapType = $data['swap_type'] ?? 'single_date';
        $singleDate = $data['swap_start_date'] ?? $data['swap_date'] ?? null;

        return new self(
            $data['requester_id'],
            $data['requester_shift_id'],
            $swapType,
            $data['id'] ?? null,
            $data['target_worker_id'] ?? null,
            $data['target_shift_id'] ?? null,
            $singleDate,
            $swapType === 'single_date' ? $singleDate : ($data['swap_start_date'] ?? null),
            $swapType === 'single_date' ? $singleDate : ($data['swap_end_date'] ?? null),
            $data['swap_dates'] ?? null,
            $data['reason'] ?? null,
            $data['metadata'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'requester_id' => $this->requester_id,
            'requester_shift_id' => $this->requester_shift_id,
            'swap_type' => $this->swap_type,
            'target_worker_id' => $this->target_worker_id,
            'target_shift_id' => $this->target_shift_id,
            'swap_date' => $this->swap_date,
            'swap_start_date' => $this->swap_start_date,
            'swap_end_date' => $this->swap_end_date,
            'swap_dates' => $this->swap_dates,
            'reason' => $this->reason,
            'metadata' => $this->metadata,
        ];
    }
}
