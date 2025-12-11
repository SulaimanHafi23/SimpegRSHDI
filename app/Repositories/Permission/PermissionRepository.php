<?php

namespace App\Repositories\Permission;

use App\Repositories\Contracts\Permission\PermissionRepositoryInterface;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Eloquent\Collection;

class PermissionRepository implements PermissionRepositoryInterface
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        private readonly Permission $model
    ) {}

    public function getAll(): Collection
    {
        return $this->model->orderBy('name')->get();
    }

    public function getGrouped(): array
    {
        $permissions = $this->getAll();
        
        $grouped = [];
        
        foreach ($permissions as $permission) {
            // Extract module from permission name (e.g., "view-workers" -> "workers")
            $parts = explode('-', $permission->name, 2);
            $action = $parts[0] ?? 'other';
            $module = $parts[1] ?? $permission->name;
            
            // Group by module
            if (!isset($grouped[$module])) {
                $grouped[$module] = [];
            }
            
            $grouped[$module][] = $permission;
        }
        
        return $grouped;
    }
}
