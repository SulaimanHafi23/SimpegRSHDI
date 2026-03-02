<?php

namespace App\Http\Controllers\Auth;

use App\DTOs\Auth\LoginDTO;
use App\Helpers\PermissionHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\Auth\AuthService;
use App\Models\AuditLog;
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

            // Log login event
            AuditLog::log('login', 'User berhasil login', $result['user']);

            // Redirect based on role
            return redirect()->intended(
                $this->getRedirectUrl($result['user'])
            );

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
        $user = auth()->user();

        // Log logout event before invalidating session
        if ($user) {
            AuditLog::log('logout', 'User logout', $user);
        }

        $this->authService->logout($user);

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
        return PermissionHelper::getDefaultRoute();
    }
}
