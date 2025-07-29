<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JadwalKerja;
use Carbon\Carbon;

class JadwalKerjaApiController extends Controller
{
    public function hariIni(Request $request)
    {
        // Cek hari sekarang (localized Indonesia, ex: Senin)
        $hariIni = ucfirst(Carbon::now()->locale('id')->isoFormat('dddd'));

        // Ambil jadwal hari ini (gunakan lowercase untuk robust)
        $jadwal = JadwalKerja::whereRaw('LOWER(hari) = ?', [strtolower($hariIni)])->first();

        if (!$jadwal) {
            return response()->json([
                'status' => false,
                'message' => 'Tidak ada jadwal kerja untuk hari ini',
                'data' => null
            ], 200);
        }

        return response()->json([
            'status' => true,
            'data' => [
                'hari'       => $jadwal->hari,
                'jam_masuk'  => $jadwal->jam_masuk,
                'jam_pulang' => $jadwal->jam_pulang,
                'aktif'      => $jadwal->aktif,
            ]
        ]);
    }
}
