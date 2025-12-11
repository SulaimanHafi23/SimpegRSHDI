<?php

namespace App\Services\Permission;

use App\Repositories\Contracts\Permission\PermissionRepositoryInterface;

class PermissionService
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        private readonly PermissionRepositoryInterface $repository
    ) {}

    public function getAll()
    {
        return $this->repository->getAll();
    }

    public function getGrouped()
    {
        return $this->repository->getGrouped();
    }
}
