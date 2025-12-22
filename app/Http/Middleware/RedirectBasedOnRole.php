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
        
        // Get user's first role
        $role = $user->roles->first()?->name;
        
        // Define admin roles
        $adminRoles = ['Super Admin', 'HR', 'Manager'];
        $isAdmin = in_array($role, $adminRoles);
        
        // If accessing /dashboard
        if ($currentPath === 'dashboard') {
            if ($isAdmin) {
                // Admin accessing /dashboard - allow
                return $next($request);
            } else {
                // Employee accessing /dashboard - redirect to employee dashboard
                return redirect()->route('employee.dashboard');
            }
        }
        
        // If accessing /employee/dashboard or /employee/*
        if (str_starts_with($currentPath, 'employee/')) {
            if ($isAdmin) {
                // Admin accessing employee routes - redirect to admin dashboard
                return redirect()->route('admin.dashboard');
            } else {
                // Employee accessing employee routes - allow
                return $next($request);
            }
        }

        // For other routes, continue normally
        return $next($request);
    }
}
