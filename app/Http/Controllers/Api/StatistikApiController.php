<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Absensi;
use App\Models\PengajuanIzin;

class StatistikApiController extends Controller
{
    public function statistikAbsensi(Request $request)
    {
        $karyawanId = $request->query('karyawan_id');
        if (!$karyawanId) {
            return response()->json(['status' => false, 'message' => 'karyawan_id wajib diisi'], 400);
        }

        // Total hadir (status = Hadir)
        $absen = Absensi::where('karyawan_id', $karyawanId)
            ->where('status', 'Hadir')
            ->count();

        // Total izin (status = Disetujui pada pengajuan_izin)
        $izin = PengajuanIzin::where('karyawan_id', $karyawanId)
            ->where('status', 'Disetujui')
            ->count();

        // Total telat (status = Telat di absensi)
        $telat = Absensi::where('karyawan_id', $karyawanId)
            ->where('status', 'Telat')
            ->count();

        return response()->json([
            'status' => true,
            'data' => [
                'absen' => $absen,
                'izin' => $izin,
                'telat' => $telat,
            ]
        ]);
    }
}
