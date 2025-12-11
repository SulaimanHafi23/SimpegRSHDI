<?php

namespace App\Repositories\Contracts\Role;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface RoleRepositoryInterface
{
    /**
     * Create a new class instance.
     */
    public function __construct();

    public function paginate(int $perPage = 15): LengthAwarePaginator;
    public function getAll(): Collection;
    public function findById(string $id);
    public function findByName(string $name);
    public function create(array $data);
    public function update(string $id, array $data): bool;
    public function delete(string $id): bool;
    public function syncPermissions(string $id, array $permissions): bool;
    public function getPermissions(string $id);
}
