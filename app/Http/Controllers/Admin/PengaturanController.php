<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Exports\KaryawanExport;
use App\Exports\AbsensiExport;
use Maatwebsite\Excel\Facades\Excel;

class PengaturanController extends Controller
{
    /**
     * Tampilkan form ganti password admin.
     */
    public function gantiPasswordForm()
    {
        return view('admin.pengaturan.ganti_password');
    }

    /**
     * Update password admin.
     */
    public function gantiPasswordUpdate(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'current_password' => ['required'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'new_password.confirmed' => 'Konfirmasi password baru tidak cocok.',
            'new_password.min' => 'Password minimal 8 karakter.'
        ]);

        // Cek password lama
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->with('error', 'Password lama tidak sesuai!');
        }

        // Cek password baru sama dengan lama
        if (Hash::check($request->new_password, $user->password)) {
            return back()->with('error', 'Password baru tidak boleh sama dengan password lama.');
        }

        // Update password
        $user->password = Hash::make($request->new_password);
        $user->save();

        return back()->with('success', 'Password berhasil diperbarui!');
    }

    // Eksport Karyawan
    public function exportKaryawan()
    {
        return Excel::download(new KaryawanExport, 'data_karyawan.xlsx');
    }

    // Eksport Absensi
    public function exportAbsensi()
    {
        return Excel::download(new AbsensiExport, 'riwayat_absensi.xlsx');
    }
}
