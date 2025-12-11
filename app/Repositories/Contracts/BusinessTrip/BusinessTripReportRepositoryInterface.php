<?php
// filepath: app/Repositories/Contracts/BusinessTrip/BusinessTripReportRepositoryInterface.php

namespace App\Repositories\Contracts\BusinessTrip;

use Illuminate\Database\Eloquent\Collection;

interface BusinessTripReportRepositoryInterface
{
    /**
     * Create a new class instance.
     */
    public function __construct();

    public function findById(string $id);
    public function getByBusinessTrip(string $businessTripId): Collection;
    public function getPendingReview(): Collection;
    public function create(array $data);
    public function update(string $id, array $data): bool;
    public function delete(string $id): bool;
    public function approve(string $id, string $userId, ?string $notes = null): bool;
    public function reject(string $id, string $userId, string $notes): bool;
}
