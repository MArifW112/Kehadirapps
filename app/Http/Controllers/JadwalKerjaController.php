<?php

namespace App\Http\Controllers;

use App\Models\JadwalKerja;
use Illuminate\Http\Request;

class JadwalKerjaController extends Controller
{
    public function index()
    {
        $jadwal = JadwalKerja::all();
        return view('admin.jadwal_kerja.index', compact('jadwal'));
    }

    public function update(Request $request, $id)
    {
        $jamMasuk = strlen($request->jam_masuk) === 5 ? $request->jam_masuk . ':00' : $request->jam_masuk;
        $jamPulang = strlen($request->jam_pulang) === 5 ? $request->jam_pulang . ':00' : $request->jam_pulang;

        $request->merge([
            'jam_masuk' => $jamMasuk,
            'jam_pulang' => $jamPulang,
        ]);

        $request->validate([
            'jam_masuk' => 'required|date_format:H:i:s',
            'jam_pulang' => 'required|date_format:H:i:s|after:jam_masuk',
            'aktif' => 'required|boolean',
        ]);

        $jadwal = JadwalKerja::findOrFail($id);
        $jadwal->update($request->only(['jam_masuk', 'jam_pulang', 'aktif']));
        return redirect()->route('admin.jadwal-kerja.index')->with('success', 'Jadwal berhasil diupdate');
    }
}
