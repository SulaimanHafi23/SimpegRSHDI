<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'redirect_role' => \App\Http\Middleware\RedirectBasedOnRole::class,
            'check.business.trip.approval' => \App\Http\Middleware\CheckBusinessTripApprovalAccess::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (TokenMismatchException $exception, Request $request) {
            $message = 'Sesi Anda sudah berakhir. Silakan login kembali.';

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                ], 419);
            }

            if ($request->routeIs('login.post') || $request->is('login')) {
                return redirect()->route('login')->with('warning', $message);
            }

            return redirect()->route('login')->with('warning', $message);
        });
    })->create();
