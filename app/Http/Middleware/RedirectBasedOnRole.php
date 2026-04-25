<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectBasedOnRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // If user not authenticated, continue
        if (!$user) {
            return $next($request);
        }

        $currentPath = $request->path();

        // If accessing /dashboard - check permission
        if ($currentPath === 'dashboard') {
            if ($user->can('dashboard.admin')) {
                return $next($request);
            } else {
                // Redirect to employee dashboard if no admin permission
                return redirect()->route('employee.dashboard');
            }
        }

        // If accessing /employee/dashboard or /employee/* - check permission
        if (str_starts_with($currentPath, 'employee/')) {
            if ($user->can('dashboard.employee') && $user->worker) {
                return $next($request);
            }

            return redirect()->route('home');
        }

        return $next($request);
    }
}
