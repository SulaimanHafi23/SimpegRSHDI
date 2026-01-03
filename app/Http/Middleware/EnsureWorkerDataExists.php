<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureWorkerDataExists
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        if (!$user || !$user->worker) {
            // Sesuaikan respons jika diperlukan, misalnya untuk API request
            return redirect()->route('employee.dashboard')
                ->with('error', 'Data pekerja tidak ditemukan.');
        }

        return $next($request);
    }
}