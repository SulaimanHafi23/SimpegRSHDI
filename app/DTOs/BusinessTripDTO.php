<?php

namespace App\DTOs;

use Carbon\Carbon;

class BusinessTripDTO
{
    public function __construct(
        public readonly ?string $id,
        public readonly string $workerId,
        public readonly string $destination,
        public readonly string $purpose,
        public readonly string $startDate,
        public readonly string $endDate,
        public readonly int $totalDays,
        public readonly ?float $transportCost,
        public readonly ?float $accommodationCost,
        public readonly ?float $mealCost,
        public readonly ?float $otherCost,
        public readonly ?float $totalCost,
        public readonly ?string $notes,
        public readonly string $status,
        public readonly ?string $approvedBy,
        public readonly ?string $approvedAt,
        public readonly ?string $rejectionReason,
    ) {}

    public static function fromRequest(array $data): self
    {
        $startDate = Carbon::parse($data['start_date']);
        $endDate = Carbon::parse($data['end_date']);
        $totalDays = $startDate->diffInDays($endDate) + 1;

        $transportCost = $data['transport_cost'] ?? 0;
        $accommodationCost = $data['accommodation_cost'] ?? 0;
        $mealCost = $data['meal_cost'] ?? 0;
        $otherCost = $data['other_cost'] ?? 0;
        $totalCost = $transportCost + $accommodationCost + $mealCost + $otherCost;

        return new self(
            id: $data['id'] ?? null,
            workerId: $data['worker_id'],
            destination: $data['destination'],
            purpose: $data['purpose'],
            startDate: $data['start_date'],
            endDate: $data['end_date'],
            totalDays: $totalDays,
            transportCost: $transportCost > 0 ? $transportCost : null,
            accommodationCost: $accommodationCost > 0 ? $accommodationCost : null,
            mealCost: $mealCost > 0 ? $mealCost : null,
            otherCost: $otherCost > 0 ? $otherCost : null,
            totalCost: $totalCost > 0 ? $totalCost : null,
            notes: $data['notes'] ?? null,
            status: $data['status'] ?? 'pending',
            approvedBy: $data['approved_by'] ?? null,
            approvedAt: $data['approved_at'] ?? null,
            rejectionReason: $data['rejection_reason'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'worker_id' => $this->workerId,
            'destination' => $this->destination,
            'purpose' => $this->purpose,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'total_days' => $this->totalDays,
            'transport_cost' => $this->transportCost,
            'accommodation_cost' => $this->accommodationCost,
            'meal_cost' => $this->mealCost,
            'other_cost' => $this->otherCost,
            'total_cost' => $this->totalCost,
            'notes' => $this->notes,
            'status' => $this->status,
            'approved_by' => $this->approvedBy,
            'approved_at' => $this->approvedAt,
            'rejection_reason' => $this->rejectionReason,
        ], fn($value) => !is_null($value));
    }
}
