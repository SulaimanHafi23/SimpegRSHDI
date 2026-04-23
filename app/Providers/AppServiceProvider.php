<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Intentionally left blank.
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Log lazy loading violations instead of throwing exceptions
        // This helps detect N+1 queries without crashing the app
        Model::handleLazyLoadingViolationUsing(function ($model, $relation) {
            Log::warning("Lazy loading [{$relation}] on model [" . get_class($model) . "]");
        });

        // Permission Blade Directives
        Blade::if('can', function ($permission) {
            return Auth::check() && Auth::user()->can($permission);
        });

        Blade::if('canany', function (...$permissions) {
            return Auth::check() && Auth::user()->hasAnyPermission($permissions);
        });

        Blade::if('role', function ($role) {
            return Auth::check() && Auth::user()->hasRole($role);
        });

        Blade::if('roleany', function (...$roles) {
            return Auth::check() && Auth::user()->hasAnyRole($roles);
        });

        Blade::if('hasallpermissions', function (...$permissions) {
            return Auth::check() && Auth::user()->hasAllPermissions($permissions);
        });

        // Check if user is Super Admin
        Blade::if('superadmin', function () {
            return Auth::check() && Auth::user()->hasRole('Super Admin');
        });

        // Check if user is HR
        Blade::if('hr', function () {
            return Auth::check() && Auth::user()->hasRole('HR');
        });

        // Check if user is Manager
        Blade::if('manager', function () {
            return Auth::check() && Auth::user()->hasRole('Manager');
        });

        // Check if user is Employee
        Blade::if('employee', function () {
            return Auth::check() && Auth::user()->hasRole('Employee');
        });
    }
}
