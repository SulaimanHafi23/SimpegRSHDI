<?php

namespace App\DTOs;

class ShiftSwapRequestDTO
{
    public function __construct(
        public readonly ?string $id = null,
        public readonly string $requester_id,
        public readonly ?string $target_worker_id = null,
        public readonly string $requester_shift_id,
        public readonly ?string $target_shift_id = null,
        public readonly ?string $reason = null,
        public readonly ?array $metadata = null,
        public readonly ?string $expires_at = null,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            $data['id'] ?? null,
            $data['requester_id'],
            $data['target_worker_id'] ?? null,
            $data['requester_shift_id'],
            $data['target_shift_id'] ?? null,
            $data['reason'] ?? null,
            $data['metadata'] ?? null,
            $data['expires_at'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'requester_id' => $this->requester_id,
            'target_worker_id' => $this->target_worker_id,
            'requester_shift_id' => $this->requester_shift_id,
            'target_shift_id' => $this->target_shift_id,
            'reason' => $this->reason,
            'metadata' => $this->metadata,
            'expires_at' => $this->expires_at,
        ];
    }
}
