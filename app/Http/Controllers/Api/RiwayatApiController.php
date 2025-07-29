<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Absensi;
use App\Models\PengajuanIzin;

class RiwayatApiController extends Controller
{
    // /api/auth/riwayat-absensi?karyawan_id=123
    public function absensi(Request $request)
    {
        $karyawan_id = $request->query('karyawan_id');
        if (!$karyawan_id) {
            return response()->json(['status' => false, 'message' => 'karyawan_id wajib dikirim.'], 422);
        }

        $absensi = Absensi::where('karyawan_id', $karyawan_id)
            ->orderBy('tanggal', 'desc')
            ->get()
            ->map(function ($row) {
                return [
                    'id' => $row->id,
                    'tanggal' => $row->tanggal,
                    'jam_masuk' => $row->jam_masuk ?? '',
                    'jam_pulang' => $row->jam_pulang ?? '',
                    'lokasi' => $row->lokasi ?? '',
                    'status' => $row->status ?? '',
                ];
            });

        return response()->json(['status' => true, 'data' => $absensi]);
    }

    // /api/auth/riwayat-izin?karyawan_id=123
    public function izin(Request $request)
    {
        $karyawan_id = $request->query('karyawan_id');
        if (!$karyawan_id) {
            return response()->json(['status' => false, 'message' => 'karyawan_id wajib dikirim.'], 422);
        }

        $izin = PengajuanIzin::where('karyawan_id', $karyawan_id)
            ->orderBy('tanggal', 'desc')
            ->get()
            ->map(function ($row) {
                return [
                    'id' => $row->id,
                    'jenis' => $row->jenis ?? '',
                    'alasan' => $row->alasan ?? '',
                    'tanggal' => $row->tanggal,
                    'status' => $row->status ?? '',
                    'foto_bukti' => $row->foto_bukti ? asset('storage/' . $row->foto_bukti) : null,
                ];
            });

        return response()->json(['status' => true, 'data' => $izin]);
    }
}
