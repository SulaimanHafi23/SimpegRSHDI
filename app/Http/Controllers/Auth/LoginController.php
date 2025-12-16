<?php

namespace App\Http\Controllers\Auth;

use App\DTOs\Auth\LoginDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\Auth\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {}

    /**
     * Show login form
     */
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    /**
     * Handle login request
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        try {
            // Create DTO from request
            $dto = LoginDTO::fromRequest($request->validated());
            
            // Authenticate user
            $result = $this->authService->login($dto);

            if (!$result['success']) {
                return back()
                    ->withInput($request->only('login', 'remember_me'))
                    ->withErrors(['login' => $result['message']]);
            }

            // Regenerate session
            $request->session()->regenerate();

            // Redirect based on role
            return redirect()->intended(
                $this->getRedirectUrl($result['user'])
            )->with('success', 'Login berhasil!');

        } catch (\Exception $e) {
            return back()
                ->withInput($request->only('login', 'remember_me'))
                ->withErrors(['login' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    /**
     * Logout user
     */
    public function logout(): RedirectResponse
    {
        $this->authService->logout(auth()->user() ?? null);
        
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Logout berhasil!');
    }

    /**
     * Redirect user based on role
     */
    private function getRedirectUrl($user): string
    {
        // Check if user has roles
        if (!$user->roles || $user->roles->isEmpty()) {
            return route('employee.dashboard');
        }

        $role = $user->roles->first()->name;

        return match($role) {
            'super-admin' => route('admin.dashboard'),
            'admin' => route('admin.dashboard'),
            'hr-manager' => route('admin.dashboard'),
            'supervisor' => route('admin.dashboard'),
            'employee' => route('employee.dashboard'),
            default => route('employee.dashboard'),
        };
    }
}
