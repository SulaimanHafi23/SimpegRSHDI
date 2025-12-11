<?php

namespace App\Repositories\Contracts;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface AbsentRepositoryInterface
{
    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator;
    public function findById(string $id);
    public function findByWorkerAndDate(string $workerId, string $date);
    public function getByWorker(string $workerId, ?string $startDate = null, ?string $endDate = null): Collection;
    public function getByDate(string $date): Collection;
    public function getByDateRange(string $startDate, string $endDate): Collection;
    public function getTodayAbsents(): Collection;
    public function getLateToday(): Collection;
    public function getAbsentToday(): Collection;
    public function create(array $data);
    public function update(string $id, array $data): bool;
    public function delete(string $id): bool;
    public function checkOut(string $id, array $data): bool;
}
