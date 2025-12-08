<?php

namespace App\Http\Controllers\Auth;

use App\DTOs\Auth\LoginDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\Auth\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {}

    /**
     * Show login form
     */
    public function showLoginForm(): View
    {
        return view('welcome');
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

            // Regenerate session
            $request->session()->regenerate();

            // Redirect based on role
            return $this->redirectBasedOnRole($result['user']);

        } catch (ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->withInput($request->only('username', 'remember'));
        }
    }

    /**
     * Logout user
     */
    public function logout(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user) {
            $this->authService->logout($user);
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Logout berhasil');
    }

    /**
     * Redirect user based on role
     */
    private function redirectBasedOnRole($user): RedirectResponse
    {
        if ($user->hasRole('Super Admin')) {
            return redirect()->route('dashboard')
                ->with('success', 'Selamat datang, Super Admin!');
        }

        if ($user->hasRole('HR')) {
            return redirect()->route('hr.dashboard')
                ->with('success', 'Selamat datang, HR!');
        }

        if ($user->hasRole('Manager')) {
            return redirect()->route('manager.dashboard')
                ->with('success', 'Selamat datang, Manager!');
        }

        // Default redirect
        return redirect()->route('dashboard')
            ->with('success', 'Login berhasil!');
    }
}
