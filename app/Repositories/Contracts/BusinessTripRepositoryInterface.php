<?php

// filepath: app/Repositories/Contracts/BusinessTrip/BusinessTripRepositoryInterface.php

namespace App\Repositories\Contracts\BusinessTrip;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface BusinessTripRepositoryInterface
{
    /**
     * Create a new class instance.
     */
    public function __construct();

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator;
    public function findById(string $id);
    public function getByWorker(string $workerId, ?string $startDate = null, ?string $endDate = null): Collection;
    public function getPending(): Collection;
    public function getActive(): Collection;
    public function create(array $data);
    public function update(string $id, array $data): bool;
    public function delete(string $id): bool;
    public function approve(string $id, string $userId): bool;
    public function reject(string $id, string $userId, string $reason): bool;
    public function checkOverlap(string $workerId, string $startDate, string $endDate, ?string $excludeId = null): bool;
}
