<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    /**
     * Show the forgot password form.
     */
    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle sending the password reset link.
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', 'Link reset kata sandi telah dikirim ke email Anda.')
            : back()->withErrors(['email' => $this->getStatusMessage($status)]);
    }

    /**
     * Get human-readable status message in Indonesian.
     */
    protected function getStatusMessage(string $status): string
    {
        return match ($status) {
            Password::INVALID_USER => 'Email tidak ditemukan dalam sistem.',
            Password::RESET_THROTTLED => 'Terlalu banyak permintaan. Silakan tunggu beberapa saat.',
            default => 'Terjadi kesalahan. Silakan coba lagi.',
        };
    }
}
