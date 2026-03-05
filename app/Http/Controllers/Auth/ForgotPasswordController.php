<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Worker;
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

        $inputEmail = strtolower(trim((string) $request->input('email')));

        $targetEmail = User::query()
            ->where('email', $inputEmail)
            ->value('email');

        if (!$targetEmail) {
            $worker = Worker::query()
                ->where('email', $inputEmail)
                ->with('user')
                ->first();

            if ($worker && $worker->user) {
                $targetEmail = $worker->user->email;
            }
        }

        if (!$targetEmail) {
            return back()->withErrors(['email' => $this->getStatusMessage(Password::INVALID_USER)]);
        }

        $status = Password::sendResetLink(
            ['email' => $targetEmail]
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
            Password::INVALID_USER => 'Email tidak ditemukan dalam sistem. Gunakan email akun yang terdaftar untuk login.',
            Password::RESET_THROTTLED => 'Terlalu banyak permintaan. Silakan tunggu beberapa saat.',
            default => 'Terjadi kesalahan. Silakan coba lagi.',
        };
    }
}
