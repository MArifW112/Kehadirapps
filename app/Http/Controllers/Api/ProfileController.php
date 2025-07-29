<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Karyawan;

class ProfileController extends Controller
{
    // Update profil karyawan
    public function update(Request $request, $id)
    {
        $karyawan = Karyawan::findOrFail($id);

        $validated = $request->validate([
            'nama_karyawan' => 'required|string|max:255',
            'email'         => 'required|email|max:255',
            'no_hp'         => 'required|string|max:25',
            'alamat'        => 'required|string|max:255',
        ]);

        $karyawan->update($validated);

        return response()->json([
            'status'   => true,
            'message'  => 'Profil berhasil diperbarui.',
            'karyawan' => $karyawan,
        ]);
    }
}
