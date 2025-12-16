<?php

namespace App\Repositories\Contracts\Master;

use App\DTOs\Master\DepartmentDTO;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface DepartmentRepositoryInterface
{
    public function getAll(array $filters = []): LengthAwarePaginator;
    public function paginate(int $perPage = 15): LengthAwarePaginator;
    public function all(): Collection;
    public function active(): Collection;
    public function findById(string $id): ?object;
    public function getByCode(string $code): ?object;
    public function getByName(string $name): ?object;
    public function create(DepartmentDTO $dto): object;
    public function update(string $id, DepartmentDTO $dto): object;
    public function delete(string $id): bool;
    public function toggleStatus(string $id): object;
    public function search(string $keyword, int $perPage = 15): LengthAwarePaginator;
    public function getHierarchy(): Collection;
    public function getChildren(string $parentId): Collection;
}
