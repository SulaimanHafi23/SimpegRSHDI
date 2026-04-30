<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class ResetPasswordController extends Controller
{
    /**
     * Show the password reset form.
     */
    public function showResetForm(Request $request, $token = null)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    /**
     * Handle the password reset.
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ], [
            'token.required'      => 'Token reset tidak valid.',
            'email.required'      => 'Email wajib diisi.',
            'email.email'         => 'Format email tidak valid.',
            'password.required'   => 'Kata sandi baru wajib diisi.',
            'password.min'        => 'Kata sandi minimal 8 karakter.',
            'password.confirmed'  => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('success', 'Kata sandi berhasil direset! Silakan login dengan kata sandi baru.')
            : back()->withErrors(['email' => $this->getStatusMessage($status)]);
    }

    /**
     * Get human-readable status message in Indonesian.
     */
    protected function getStatusMessage(string $status): string
    {
        return match ($status) {
            Password::INVALID_USER => 'Email tidak ditemukan dalam sistem.',
            Password::INVALID_TOKEN => 'Token reset tidak valid atau sudah kedaluwarsa.',
            Password::RESET_THROTTLED => 'Terlalu banyak permintaan. Silakan tunggu beberapa saat.',
            default => 'Terjadi kesalahan. Silakan coba lagi.',
        };
    }
}
