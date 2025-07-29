<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\TokenMismatchException; // <<< Import ini
use Illuminate\Http\Request; // <<< Import ini


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // --- TAMBAHKAN BARIS INI DI SINI ---
        $middleware->trustProxies(at: '*');
        // ---------------------------------

        // Anda juga bisa menambahkan alias middleware di sini jika belum ada
        $middleware->alias([
            'admin' => \App\Http\Middleware\RoleAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // <<< TAMBAHKAN BLOK INI
        $exceptions->renderable(function (TokenMismatchException $e, Request $request) {
            // Jika ini permintaan API, mungkin kembalikan JSON error
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Token CSRF tidak valid atau kadaluarsa. Silakan login kembali.'], 419);
            }

            // Untuk permintaan web, redirect ke halaman login dengan pesan error
            return redirect('/')->with('error', 'Sesi Anda telah berakhir. Silakan login kembali.');
        });
        // >>> AKHIR BLOK INI
    })->create();
