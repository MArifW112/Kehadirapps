<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
// HAPUS BARIS INI: use Throwable; // <<< HAPUS!

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // --- Pastikan trustProxies ada di sini ---
        $middleware->trustProxies(at: '*');
        // ---------------------------------

        $middleware->alias([
            'admin' => \App\Http\Middleware\RoleAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->renderable(function (TokenMismatchException $e, Request $request) {
            Log::error('DEBUG: TokenMismatchException TERPICU untuk Logout!', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'expectsJson' => $request->expectsJson(),
                'referrer' => $request->headers->get('referer'),
            ]);

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Token CSRF tidak valid atau kadaluarsa. Silakan login kembali.'], 419);
            }

            return redirect('/')->with('error', 'Sesi Anda telah berakhir. Silakan login kembali.');
        });
        // Pastikan exception handler lain (jika ada) masih menggunakan Throwable
        // Misalnya:
        $exceptions->reportable(function (Throwable $e) { // Throwable tetap digunakan di sini
            //
        });
    })->create();
