<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\Absensi;
use App\Models\JadwalKerja;
use App\Models\PengajuanIzin;
use Illuminate\Http\Request;
use Carbon\Carbon;

class RiwayatAbsensiController extends Controller
{
    public function index(Request $request)
    {
        // Filter tanggal (default hari ini)
        $tanggal = $request->input('tanggal') ?? Carbon::today()->toDateString();
        $hariIni = ucfirst(Carbon::parse($tanggal)->locale('id')->isoFormat('dddd'));

        // Cari jadwal kerja aktif hari itu
        $jadwal = JadwalKerja::whereRaw('LOWER(hari) = ?', [strtolower($hariIni)])
            ->where('aktif', 1)
            ->first();

        // ===== Pengecekan Hari Libur =====
        if (!$jadwal) {
            // Tidak ada jadwal aktif (LIBUR)
            $karyawans = Karyawan::whereDate('created_at', '<=', $tanggal)->get();
            $absensis = [];
            foreach ($karyawans as $karyawan) {
                $absensis[] = [
                    'nama_karyawan' => $karyawan->nama_karyawan,
                    'tanggal'       => $tanggal,
                    'jam_masuk'     => '-',
                    'foto'          => null,
                    'jam_pulang'    => '-',
                    'status'        => 'Libur',
                ];
            }
            return view('admin.riwayat_absensi.index', compact('absensis', 'tanggal'));
        }

        // ===== HARI KERJA (jadwal aktif) =====
        // Ambil karyawan yang sudah aktif sebelum/sama tanggal filter
        $karyawans = Karyawan::with([
            'absensis' => function ($q) use ($tanggal) {
                $q->where('tanggal', $tanggal);
            }
        ])
        ->whereDate('created_at', '<=', $tanggal)
        ->get();

        $absensis = [];
        foreach ($karyawans as $karyawan) {
            $absen = $karyawan->absensis->first();

            // Cek ada izin?
            $izin = PengajuanIzin::where('karyawan_id', $karyawan->id)
                ->where('tanggal', $tanggal)
                ->where('status', 'Disetujui')
                ->first();

            $status = 'Alpha';
            if ($izin) {
                $status = 'Izin';
            } elseif ($absen) {
                if ($absen->jam_masuk) {
                    // Cek telat
                    if ($absen->jam_masuk > Carbon::parse($jadwal->jam_masuk)->addMinutes(30)->format('H:i:s')) {
                        $status = 'Telat';
                    } else {
                        $status = 'Hadir';
                    }
                }
            } else {
                // Tidak absen dan tidak izin
                $now = Carbon::now()->format('H:i:s');
                $deadlineAlpha = Carbon::parse($jadwal->jam_masuk)->addHour()->format('H:i:s');
                // Sudah lewat deadline atau buka riwayat hari sebelumnya
                if ($now >= $deadlineAlpha || $tanggal != Carbon::today()->toDateString()) {
                    // Cek sudah pernah disimpan alpha atau belum
                    $existingAbsensi = Absensi::where('karyawan_id', $karyawan->id)
                        ->where('tanggal', $tanggal)
                        ->first();
                    if (!$existingAbsensi) {
                        Absensi::create([
                            'karyawan_id' => $karyawan->id,
                            'tanggal'     => $tanggal,
                            'status'      => 'Alpha',
                            'jam_masuk'   => null,
                            'jam_pulang'  => null,
                            'foto'        => null,
                        ]);
                    }
                    $status = 'Alpha';
                } else {
                    $status = 'Belum Absen';
                }
            }

            // Ambil ulang absen (biar update jam_pulang & status terbaru)
            $absen = Absensi::where('karyawan_id', $karyawan->id)
                ->where('tanggal', $tanggal)
                ->first();

            $absensis[] = [
                'nama_karyawan' => $karyawan->nama_karyawan,
                'tanggal'       => $tanggal,
                'jam_masuk'     => $absen->jam_masuk ?? '-',
                'foto'          => $absen->foto ?? null,
                'jam_pulang'    => $absen->jam_pulang ?? '-',
                'status'        => $absen->status ?? $status,
            ];
        }

        return view('admin.riwayat_absensi.index', compact('absensis', 'tanggal'));
    }
}
