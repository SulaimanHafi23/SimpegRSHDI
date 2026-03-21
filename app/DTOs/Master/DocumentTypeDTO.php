<?php
// filepath: app/DTOs/Master/DocumentTypeDTO.php

namespace App\DTOs\Master;

class DocumentTypeDTO
{
    public function __construct(
        public readonly ?string $id,
        public readonly string $name,
        public readonly ?string $description,
        public readonly ?string $file_format,
        public readonly ?int $max_file_size,
        public readonly string $employment_category,
        public readonly string $process_type,
        public readonly ?int $expiration_buffer_days,
        public readonly ?string $requirement_notes,
        public readonly ?string $source_document_type_id,
        public readonly bool $is_required,
        public readonly bool $is_active,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            name: $data['name'],
            description: $data['description'] ?? null,
            file_format: $data['file_format'] ?? ($data['allowed_extensions'] ?? null),
            max_file_size: isset($data['max_file_size']) ? (int) $data['max_file_size'] : null,
            employment_category: $data['employment_category'] ?? 'all',
            process_type: $data['process_type'] ?? 'onboarding',
            expiration_buffer_days: isset($data['expiration_buffer_days']) ? (int) $data['expiration_buffer_days'] : 0,
            requirement_notes: $data['requirement_notes'] ?? null,
            source_document_type_id: $data['source_document_type_id'] ?? null,
            is_required: (bool) ($data['is_required'] ?? true),
            is_active: $data['is_active'] ?? true,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'file_format' => $this->file_format,
            'max_file_size' => $this->max_file_size,
            'employment_category' => $this->employment_category,
            'process_type' => $this->process_type,
            'expiration_buffer_days' => $this->expiration_buffer_days,
            'requirement_notes' => $this->requirement_notes,
            'source_document_type_id' => $this->source_document_type_id,
            'is_required' => $this->is_required,
            'is_active' => $this->is_active,
        ], fn($value) => $value !== null);
    }
}