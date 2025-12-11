<?php

// filepath: app/Repositories/Contracts/BerkasRepositoryInterface.php

namespace App\Repositories\Contracts\Document;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface BerkasRepositoryInterface
{
    /**
     * Create a new class instance.
     */
    public function __construct();

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator;
    public function findById(string $id);
    public function findByWorker(string $workerId): Collection;
    public function findByWorkerAndRequirement(string $workerId, string $fileRequirementId);
    public function getPending(): Collection;
    public function create(array $data);
    public function update(string $id, array $data): bool;
    public function delete(string $id): bool;
    public function verify(string $id, string $userId, ?string $notes = null): bool;
    public function reject(string $id, string $userId, string $notes): bool;
}
