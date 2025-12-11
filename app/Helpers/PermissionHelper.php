<?php

namespace App\Helpers;

class PermissionHelper
{
    /**
     * Check if current user has permission
     */
    public static function can(string $permission): bool
    {
        return auth()->check() && auth()->user()->can($permission);
    }

    /**
     * Check if current user has any of the permissions
     */
    public static function canAny(array $permissions): bool
    {
        return auth()->check() && auth()->user()->hasAnyPermission($permissions);
    }

    /**
     * Check if current user has all permissions
     */
    public static function canAll(array $permissions): bool
    {
        return auth()->check() && auth()->user()->hasAllPermissions($permissions);
    }

    /**
     * Check if current user has role
     */
    public static function hasRole(string $role): bool
    {
        return auth()->check() && auth()->user()->hasRole($role);
    }

    /**
     * Check if current user has any role
     */
    public static function hasAnyRole(array $roles): bool
    {
        return auth()->check() && auth()->user()->hasAnyRole($roles);
    }

    /**
     * Get user's permissions
     */
    public static function getUserPermissions(): array
    {
        if (!auth()->check()) {
            return [];
        }

        return auth()->user()->getAllPermissions()->pluck('name')->toArray();
    }

    /**
     * Get user's roles
     */
    public static function getUserRoles(): array
    {
        if (!auth()->check()) {
            return [];
        }

        return auth()->user()->getRoleNames()->toArray();
    }

    /**
     * Check if user is Super Admin
     */
    public static function isSuperAdmin(): bool
    {
        return auth()->check() && auth()->user()->hasRole('Super Admin');
    }

    /**
     * Check if user is HR
     */
    public static function isHR(): bool
    {
        return auth()->check() && auth()->user()->hasRole('HR');
    }

    /**
     * Check if user is Manager
     */
    public static function isManager(): bool
    {
        return auth()->check() && auth()->user()->hasRole('Manager');
    }

    /**
     * Check if user is Employee
     */
    public static function isEmployee(): bool
    {
        return auth()->check() && auth()->user()->hasRole('Employee');
    }

    /**
     * Check if viewing own data
     */
    public static function isOwnData(string $workerId): bool
    {
        return auth()->check() && auth()->user()->worker_id === $workerId;
    }

    /**
     * Get permission-based redirect route
     */
    public static function getDefaultRoute(): string
    {
        if (!auth()->check()) {
            return route('login');
        }

        $user = auth()->user();

        if ($user->hasRole('Super Admin')) {
            return route('admin.dashboard');
        }

        if ($user->hasRole('HR')) {
            return route('hr.dashboard');
        }

        if ($user->hasRole('Manager')) {
            return route('manager.dashboard');
        }

        return route('employee.dashboard');
    }

    /**
     * Format permissions for select dropdown
     */
    public static function getPermissionsForSelect(): array
    {
        $permissions = \Spatie\Permission\Models\Permission::all();
        return $permissions->pluck('name', 'id')->toArray();
    }

    /**
     * Format roles for select dropdown
     */
    public static function getRolesForSelect(): array
    {
        $roles = \Spatie\Permission\Models\Role::all();
        return $roles->pluck('name', 'id')->toArray();
    }

    /**
     * Group permissions by module
     */
    public static function getGroupedPermissions(): array
    {
        $permissions = \Spatie\Permission\Models\Permission::all();
        $grouped = [];

        foreach ($permissions as $permission) {
            $parts = explode('-', $permission->name, 2);
            $action = $parts[0] ?? 'other';
            $module = $parts[1] ?? $permission->name;

            if (!isset($grouped[$module])) {
                $grouped[$module] = [];
            }

            $grouped[$module][] = $permission;
        }

        return $grouped;
    }
}
