<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

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
        //
    })->create();
