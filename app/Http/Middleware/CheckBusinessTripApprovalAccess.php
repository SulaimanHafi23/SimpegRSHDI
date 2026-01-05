<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckBusinessTripApprovalAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        
        // Allow Super Admin or users with approve-business-trips permission
        if ($user && ($user->hasRole('Super Admin') || $user->can('approve-business-trips'))) {
            return $next($request);
        }

        abort(403, 'Anda tidak memiliki akses untuk menyetujui perjalanan bisnis.');
    }
}
