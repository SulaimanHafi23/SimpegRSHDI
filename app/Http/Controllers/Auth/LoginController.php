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
        return view('welcome');
    }

    /**
     * Handle login request
     */
    public function login(LoginRequest $request): RedirectResponse
    {

        // dd($request->validated());
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
        $role = $user->roles->first()?->name;

        return match($role) {
            'Super Admin' => route('admin.dashboard'),
            'HR' => route('hr.dashboard'),
            'Finance' => route('finance.dashboard'),
            'Manager' => route('manager.dashboard'),
            'Direktur' => route('direktur.dashboard'),
            default => route('user.dashboard'),
        };
    }
}
