<?php

namespace App\Repositories\Contracts\Master;

use App\DTOs\Master\ShiftDTO;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ShiftRepositoryInterface
{
    public function getAll(array $filters = []): LengthAwarePaginator;
    public function paginate(int $perPage = 15): LengthAwarePaginator;
    public function all(): Collection;
    public function active(): Collection;
    public function findById(string $id): ?object;
    public function getByName(string $name): ?object;
    public function create(ShiftDTO $dto): object;
    public function update(string $id, ShiftDTO $dto): object;
    public function delete(string $id): bool;
    public function toggleStatus(string $id): object;
    public function search(string $keyword, int $perPage = 15): LengthAwarePaginator;
    public function getOvernightShifts(): Collection;
}
