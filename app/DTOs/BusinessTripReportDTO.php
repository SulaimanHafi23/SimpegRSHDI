<?php

namespace App\DTOs;

class BusinessTripReportDTO
{
    public function __construct(
        public readonly ?string $id,
        public readonly string $businessTripId,
        public readonly string $reportDate,
        public readonly string $activities,
        public readonly string $results,
        public readonly ?string $attachmentUrl,
        public readonly ?string $notes,
        public readonly string $status,
        public readonly ?string $reviewedBy,
        public readonly ?string $reviewedAt,
        public readonly ?string $reviewNotes,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            businessTripId: $data['business_trip_id'],
            reportDate: $data['report_date'],
            activities: $data['activities'],
            results: $data['results'],
            attachmentUrl: $data['attachment_url'] ?? null,
            notes: $data['notes'] ?? null,
            status: $data['status'] ?? 'pending',
            reviewedBy: $data['reviewed_by'] ?? null,
            reviewedAt: $data['reviewed_at'] ?? null,
            reviewNotes: $data['review_notes'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'business_trip_id' => $this->businessTripId,
            'report_date' => $this->reportDate,
            'activities' => $this->activities,
            'results' => $this->results,
            'attachment_url' => $this->attachmentUrl,
            'notes' => $this->notes,
            'status' => $this->status,
            'reviewed_by' => $this->reviewedBy,
            'reviewed_at' => $this->reviewedAt,
            'review_notes' => $this->reviewNotes,
        ], fn($value) => !is_null($value));
    }
}
