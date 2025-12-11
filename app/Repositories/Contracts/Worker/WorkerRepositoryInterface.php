<?php

namespace App\Repositories\Contracts\Worker;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface WorkerRepositoryInterface
{
    /**
     * Create a new class instance.
     */
    public function __construct();

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator;
    public function all(): Collection;
    public function active(): Collection;
    public function findById(string $id);
    public function findByNik(string $nik);
    public function findByEmail(string $email);
    public function create(array $data);
    public function update(string $id, array $data): bool;
    public function delete(string $id): bool;
    public function search(string $keyword, int $perPage = 15): LengthAwarePaginator;
    public function getByPosition(string $positionId): Collection;
    public function getByStatus(string $status): Collection;
    public function getBirthdaysThisMonth(): Collection;
    public function getContractExpiring(int $days = 30): Collection;
}
