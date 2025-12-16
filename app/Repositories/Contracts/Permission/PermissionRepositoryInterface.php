<?php

namespace App\Repositories\Contracts\Permission;

use Illuminate\Database\Eloquent\Collection;

interface PermissionRepositoryInterface
{
    public function getAll(): Collection;
    public function getGrouped(): array;
}
