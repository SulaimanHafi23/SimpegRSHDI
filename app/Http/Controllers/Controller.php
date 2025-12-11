<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Gate;
use Closure;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Middleware definitions (for backward compatibility with Laravel 11 style)
     */
    protected array $middlewareDefinitions = [];

    /**
     * Define middleware for controller (Laravel 11 style compatibility)
     * 
     * @param array|string|Closure $middleware
     * @param array $options
     * @return \Illuminate\Routing\ControllerMiddlewareOptions|\App\Http\Controllers\ControllerMiddlewareOptions
     */
    public function middleware($middleware, array $options = []): mixed
    {
        // Handle Laravel 11 style (fluent API)
        if (empty($options)) {
            $middlewareOptions = new ControllerMiddlewareOptions($middleware);
            $this->middlewareDefinitions[] = $middlewareOptions;
            return $middlewareOptions;
        }
        
        // Handle Laravel 12 style (with options array)
        $middlewareOptions = new ControllerMiddlewareOptions($middleware);
        
        if (isset($options['only'])) {
            $middlewareOptions->only($options['only']);
        }
        
        if (isset($options['except'])) {
            $middlewareOptions->except($options['except']);
        }
        
        $this->middlewareDefinitions[] = $middlewareOptions;
        
        return $middlewareOptions;
    }

    /**
     * Get all middleware definitions
     */
    public function getMiddlewareDefinitions(): array
    {
        return $this->middlewareDefinitions;
    }

    /**
     * Authorize user has permission
     */
    protected function authorizePermission(string $permission): void
    {
        if (!auth()->check()) {
            abort(401, 'Anda harus login terlebih dahulu.');
        }

        if (!auth()->user()->can($permission)) {
            abort(403, 'Anda tidak memiliki akses untuk melakukan aksi ini.');
        }
    }

    /**
     * Authorize user has any of the permissions
     */
    protected function authorizeAnyPermission(array $permissions): void
    {
        if (!auth()->check()) {
            abort(401, 'Anda harus login terlebih dahulu.');
        }

        if (!auth()->user()->hasAnyPermission($permissions)) {
            abort(403, 'Anda tidak memiliki akses untuk melakukan aksi ini.');
        }
    }

    /**
     * Authorize user has all permissions
     */
    protected function authorizeAllPermissions(array $permissions): void
    {
        if (!auth()->check()) {
            abort(401, 'Anda harus login terlebih dahulu.');
        }

        if (!auth()->user()->hasAllPermissions($permissions)) {
            abort(403, 'Anda tidak memiliki akses untuk melakukan aksi ini.');
        }
    }

    /**
     * Authorize user has role
     */
    protected function authorizeRole(string $role): void
    {
        if (!auth()->check()) {
            abort(401, 'Anda harus login terlebih dahulu.');
        }

        if (!auth()->user()->hasRole($role)) {
            abort(403, 'Anda tidak memiliki akses untuk melakukan aksi ini.');
        }
    }

    /**
     * Check if user is viewing own data
     */
    protected function isOwnData(?string $workerId): bool
    {
        if (!auth()->check()) {
            return false;
        }

        return auth()->user()->worker_id === $workerId;
    }

    /**
     * Authorize own data or has permission
     */
    protected function authorizeOwnOrPermission(string $workerId, string $permission): void
    {
        if (!auth()->check()) {
            abort(401, 'Anda harus login terlebih dahulu.');
        }

        if (!$this->isOwnData($workerId) && !auth()->user()->can($permission)) {
            abort(403, 'Anda tidak memiliki akses untuk melakukan aksi ini.');
        }
    }
}

/**
 * Controller Middleware Options Helper Class
 * This class provides fluent API for middleware options (Laravel 11 compatibility)
 */
class ControllerMiddlewareOptions
{
    protected mixed $middleware;
    protected array $only = [];
    protected array $except = [];

    public function __construct(mixed $middleware)
    {
        $this->middleware = $middleware;
    }

    /**
     * Set the controller methods the middleware should apply to
     */
    public function only(string|array $methods): self
    {
        $this->only = is_array($methods) ? $methods : func_get_args();
        return $this;
    }

    /**
     * Set the controller methods the middleware should exclude
     */
    public function except(string|array $methods): self
    {
        $this->except = is_array($methods) ? $methods : func_get_args();
        return $this;
    }

    /**
     * Get the middleware
     */
    public function getMiddleware(): mixed
    {
        return $this->middleware;
    }

    /**
     * Get the methods the middleware should apply to
     */
    public function getOnly(): array
    {
        return $this->only;
    }

    /**
     * Get the methods the middleware should exclude
     */
    public function getExcept(): array
    {
        return $this->except;
    }
}
