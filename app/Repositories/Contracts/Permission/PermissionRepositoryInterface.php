<?php

namespace App\Repositories\Contracts\Permission;

use Illuminate\Database\Eloquent\Collection;

interface PermissionRepositoryInterface
{
    /**
     * Create a new class instance.
     */
    public function __construct();

    public function getAll(): Collection;
    public function getGrouped(): array;
}
