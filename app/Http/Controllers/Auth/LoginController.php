<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\PermissionHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function __construct() {}

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
            $validated = $request->validated();
            $remember = (bool) ($validated['remember_me'] ?? false);

            $login = $validated['login'] ?? '';
            $credentials = [
                'password' => (string) ($validated['password'] ?? ''),
            ];

            if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
                $credentials['email'] = $login;
                $loginField = 'email';
            } else {
                $credentials['username'] = $login;
                $loginField = 'username';
            }

            $user = User::where($loginField, $credentials[$loginField])->first();

            if (!$user) {
                $result = [
                    'success' => false,
                    'message' => 'Username atau password tidak sesuai.',
                ];
            } elseif (!$user->is_active) {
                $result = [
                    'success' => false,
                    'message' => 'Akun Anda tidak aktif. Silakan hubungi administrator.',
                ];
            } elseif (!Hash::check($credentials['password'], $user->password)) {
                $result = [
                    'success' => false,
                    'message' => 'Username atau password tidak sesuai.',
                ];
            } elseif (!Auth::attempt($credentials, $remember)) {
                $result = [
                    'success' => false,
                    'message' => 'Gagal melakukan autentikasi.',
                ];
            } else {
                $user->load(['worker.department', 'roles.permissions']);
                $user->update(['last_login' => now()]);

                $result = [
                    'success' => true,
                    'message' => 'Login berhasil!',
                    'user' => $user,
                ];
            }

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
        $user = Auth::user();

        // Log logout event before invalidating session
        if ($user) {
            AuditLog::log('logout', 'User logout', $user);
        }

        Auth::logout();

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
