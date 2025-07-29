<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\TokenMismatchException; // <<< Pastikan ini ada
use Illuminate\Http\Request; // <<< Pastikan ini ada
use Illuminate\Support\Facades\Log; // <<< Pastikan ini ada
use Throwable; // <<< Pastikan ini ada

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

        // Anda juga bisa menambahkan alias middleware di sini jika belum ada
        $middleware->alias([
            'admin' => \App\Http\Middleware\RoleAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // <<< BLOK PENANGANAN EXCEPTION ANDA >>>
        $exceptions->renderable(function (TokenMismatchException $e, Request $request) {
            // Tambahkan log diagnostik ini untuk melihat apakah handler terpicu
            Log::error('DEBUG: TokenMismatchException TERPICU untuk Logout!', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'expectsJson' => $request->expectsJson(),
                // Hati-hati dengan logging header lengkap di produksi karena bisa sensitif data
                'referrer' => $request->headers->get('referer'),
            ]);

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Token CSRF tidak valid atau kadaluarsa. Silakan login kembali.'], 419);
            }

            // Untuk permintaan web, redirect ke halaman login dengan pesan error
            return redirect('/')->with('error', 'Sesi Anda telah berakhir. Silakan login kembali.');
        });
        // <<< AKHIR BLOK PENANGANAN EXCEPTION >>>

        // Contoh: Exception handler lain (jika ada)
        $exceptions->reportable(function (Throwable $e) {
            //
        });
    })->create();
