<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Permission Blade Directives
        Blade::if('can', function ($permission) {
            return auth()->check() && auth()->user()->can($permission);
        });

        Blade::if('canany', function (...$permissions) {
            return auth()->check() && auth()->user()->hasAnyPermission($permissions);
        });

        Blade::if('role', function ($role) {
            return auth()->check() && auth()->user()->hasRole($role);
        });

        Blade::if('roleany', function (...$roles) {
            return auth()->check() && auth()->user()->hasAnyRole($roles);
        });

        Blade::if('hasallpermissions', function (...$permissions) {
            return auth()->check() && auth()->user()->hasAllPermissions($permissions);
        });

        // Check if user is Super Admin
        Blade::if('superadmin', function () {
            return auth()->check() && auth()->user()->hasRole('Super Admin');
        });

        // Check if user is HR
        Blade::if('hr', function () {
            return auth()->check() && auth()->user()->hasRole('HR');
        });

        // Check if user is Manager
        Blade::if('manager', function () {
            return auth()->check() && auth()->user()->hasRole('Manager');
        });

        // Check if user is Employee
        Blade::if('employee', function () {
            return auth()->check() && auth()->user()->hasRole('Employee');
        });
    }
}
