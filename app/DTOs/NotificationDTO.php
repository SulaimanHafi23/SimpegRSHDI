<?php

namespace App\DTOs;

class NotificationDTO
{
    public function __construct(
        public readonly ?string $id,
        public readonly string $notifiable_type,
        public readonly string $notifiable_id,
        public readonly string $type,
        public readonly ?array $data,
        public readonly ?string $read_at,
    ) {}

    public static function fromRequest(array $data): self
    {
        $payload = $data['data'] ?? [];

        if (!isset($payload['title']) && isset($data['title'])) {
            $payload['title'] = $data['title'];
        }

        if (!isset($payload['message']) && isset($data['message'])) {
            $payload['message'] = $data['message'];
        }

        return new self(
            id: $data['id'] ?? null,
            notifiable_type: $data['notifiable_type'] ?? \App\Models\User::class,
            notifiable_id: $data['notifiable_id'] ?? $data['user_id'],
            type: $data['type'],
            data: $payload,
            read_at: $data['read_at'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'notifiable_type' => $this->notifiable_type,
            'notifiable_id' => $this->notifiable_id,
            'type' => $this->type,
            'data' => $this->data,
            'read_at' => $this->read_at,
        ], fn($value) => $value !== null);
    }
}
