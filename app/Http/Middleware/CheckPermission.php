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
        \Log::info('=== CheckPermission Middleware START ===', [
            'permission_required' => $permission,
            'route' => $request->route()->getName(),
            'url' => $request->url(),
        ]);

        if (!auth()->check()) {
            \Log::warning('User not authenticated');
            return redirect()->route('login')
                ->with('error', 'Silakan login terlebih dahulu');
        }

        $user = auth()->user();

        // Log for debugging
        \Log::info('CheckPermission User Details', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'user_username' => $user->username,
            'permission' => $permission,
            'has_super_admin_role' => $user->hasRole('Super Admin'),
            'can_permission' => $user->can($permission),
            'all_roles' => $user->roles->pluck('name')->toArray(),
        ]);

        // Super Admin always has access
        if ($user->hasRole('Super Admin')) {
            \Log::info('✅ Super Admin bypass granted');
            return $next($request);
        }

        if (!$user->can($permission)) {
            \Log::warning('❌ Permission denied', [
                'user' => $user->email,
                'permission' => $permission,
                'user_permissions' => $user->getAllPermissions()->pluck('name')->toArray(),
            ]);
            abort(403, 'Anda tidak memiliki akses untuk halaman ini. Required: ' . $permission);
        }

        \Log::info('✅ Permission granted via permission check');
        return $next($request);
    }
}
