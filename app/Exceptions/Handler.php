<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [];

    /**
     * Report or log an exception.
     */
    public function register(): void
    {
        // Use the base handler's report logic
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $e
     */
    public function render($request, Throwable $e): Response|RedirectResponse
    {
        // Handle CSRF token mismatch (419 Page Expired)
        if ($e instanceof TokenMismatchException) {
            Log::warning('TokenMismatchException: CSRF token expired for user', [
                'url' => $request->fullUrl(),
                'user_id' => auth()->id()
            ]);

            // If it's an AJAX request, return JSON
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Session Anda telah berakhir. Silakan refresh halaman.',
                    'error' => 'token_mismatch'
                ], 419);
            }

            // Clear any stale intended URL and send the user back to login.
            $request->session()->forget('url.intended');
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('error', 'Session Anda telah berakhir. Silakan login kembali.');
        }

        // Handle oversized POST (HTTP 413) with a friendly message
        if ($e instanceof PostTooLargeException) {
            Log::warning('PostTooLargeException: request exceeded PHP post_max_size or upload_max_filesize');

            // If it's an AJAX request, return JSON
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Ukuran upload terlalu besar. Maks 10MB.'], 413);
            }

            // Otherwise redirect back with an error flash
            return redirect()->back()->withInput()->with('error', 'Ukuran upload terlalu besar. Maks 10MB.');
        }

        return parent::render($request, $e);
    }
}
