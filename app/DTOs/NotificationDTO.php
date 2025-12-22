<?php

namespace App\DTOs;

class NotificationDTO
{
    public function __construct(
        public readonly ?string $id,
        public readonly string $user_id,
        public readonly string $type,
        public readonly string $title,
        public readonly string $message,
        public readonly ?array $data,
        public readonly ?string $read_at,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            user_id: $data['user_id'],
            type: $data['type'],
            title: $data['title'],
            message: $data['message'],
            data: $data['data'] ?? null,
            read_at: $data['read_at'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'user_id' => $this->user_id,
            'type' => $this->type,
            'title' => $this->title,
            'message' => $this->message,
            'data' => $this->data,
            'read_at' => $this->read_at,
        ], fn($value) => $value !== null);
    }
}
