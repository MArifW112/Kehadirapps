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

        // [PERBAIKAN] Total hadir dihitung dari status 'Hadir' dan 'Telat'
        // karena karyawan yang telat tetap dianggap hadir pada hari itu.
        $absen = Absensi::where('karyawan_id', $karyawanId)
            ->whereIn('status', ['Hadir', 'Telat'])
            ->count();

        // Total izin (status = Disetujui pada pengajuan_izin) - INI SUDAH BENAR
        $izin = PengajuanIzin::where('karyawan_id', $karyawanId)
            ->where('status', 'Disetujui')
            ->count();

        // [PERUBAHAN UTAMA] Mengganti perhitungan 'Telat' menjadi 'Alpha'
        $alpha = Absensi::where('karyawan_id', $karyawanId)
            ->where('status', 'Alpha')
            ->count();

        return response()->json([
            'status' => true,
            'data' => [
                'absen' => $absen,
                'izin' => $izin,
                'alpha' => $alpha, // Key dan value diubah dari 'telat' menjadi 'alpha'
            ]
        ]);
    }
}
