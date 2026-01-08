<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login')
                ->with('error', 'Silakan login terlebih dahulu');
        }

        // Log for debugging
        \Log::info('CheckPermission Middleware', [
            'user' => auth()->user()->email,
            'permission' => $permission,
            'route' => $request->route()->getName(),
            'method' => $request->method(),
            'has_super_admin_role' => auth()->user()->hasRole('Super Admin'),
            'can_permission' => auth()->user()->can($permission),
        ]);

        // Super Admin always has access
        if (auth()->user()->hasRole('Super Admin')) {
            \Log::info('Super Admin bypass granted');
            return $next($request);
        }

        if (!auth()->user()->can($permission)) {
            \Log::warning('Permission denied', [
                'user' => auth()->user()->email,
                'permission' => $permission,
            ]);
            abort(403, 'Anda tidak memiliki akses untuk halaman ini');
        }

        return $next($request);
    }
}
