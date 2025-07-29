<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log; // <<< Pastikan ini ada

class RoleAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            // Log untuk melihat detail user yang terautentikasi
            Log::info('RoleAdmin check: User authenticated.', [
                'user_id' => auth()->user()->id,
                'user_email' => auth()->user()->email,
                'user_role_from_db' => auth()->user()->role, // <<< Ini yang paling penting
                'is_admin_comparison' => (auth()->user()->role === 'admin') // <<< Hasil perbandingan
            ]);

            if (auth()->user()->role === 'admin') {
                return $next($request);
            } else {
                Log::warning('RoleAdmin check: User role is not "admin". Redirecting.', [
                    'user_id' => auth()->user()->id,
                    'user_role_from_db' => auth()->user()->role
                ]);
            }
        } else {
            Log::warning('RoleAdmin check: User not authenticated, redirecting to login.');
        }

        return redirect('/')->with('error', 'Akses ditolak');
    }
}
