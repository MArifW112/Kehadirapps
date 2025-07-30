<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse; // <<< Pastikan ini di-import!
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse; // Jika Anda menggunakan Inertia untuk create()


class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request)
    {
        $request->authenticate();
        $request->session()->regenerate();
        \Log::info('LOGIN BERHASIL', [
            'user' => Auth::user()
        ]);

        return redirect('/admin/dashboard'); // langsung saja, jangan pakai intended
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse // <<< UBAH TIPE KEMBALIAN KE RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('login.show'); // <<< UBAH KE INI UNTUK REDIRECT
    }

}
