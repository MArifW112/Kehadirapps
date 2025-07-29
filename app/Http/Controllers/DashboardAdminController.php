<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Absensi;
use App\Models\Karyawan;
use App\Models\PengajuanIzin;
use Illuminate\Support\Carbon;

class DashboardAdminController extends Controller
{
    public function index()
    {
        $tanggalHariIni = Carbon::now()->toDateString();

        // Total semua karyawan
        $totalKaryawan = Karyawan::count();

        // Izin yang masih pending (status menunggu)
        $izinPending = PengajuanIzin::where('status', 'menunggu')->count();

        // Absensi hari ini (yang hadir)
        $absensiHariIni = Absensi::whereDate('tanggal', $tanggalHariIni)
            ->where('status', 'Hadir')
            ->count();

        // Izin sakit hari ini
        $izinSakit = PengajuanIzin::whereDate('tanggal', $tanggalHariIni)
            ->where('jenis', 'sakit')
            ->where('status', 'disetujui')
            ->count();

        // Izin cuti hari ini
        $izinCuti = Absensi::whereDate('tanggal', $tanggalHariIni)
            ->where('status', 'Cuti')
            ->count();

        // Izin lain-lain
        $izinLain = Absensi::whereDate('tanggal', $tanggalHariIni)
            ->whereNotIn('status', ['Hadir', 'Izin', 'Cuti', 'Alpha'])
            ->count();

        // Tidak hadir
        $totalAbsensiHariIni = Absensi::whereDate('tanggal', $tanggalHariIni)->count();
        $tidakHadir = $totalKaryawan - $totalAbsensiHariIni;

        // Log absensi terbaru
        $logAbsensi = Absensi::with('karyawan')
            ->whereDate('tanggal', $tanggalHariIni)
            ->latest()
            ->take(10)
            ->get();

        // Pengajuan izin terbaru (pending dan 5 terbaru, ubah sesuai kebutuhan)
        $pengajuanIzinTerbaru = PengajuanIzin::with('karyawan')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalKaryawan',
            'izinPending',
            'absensiHariIni',
            'izinSakit',
            'izinCuti',
            'izinLain',
            'tidakHadir',
            'logAbsensi',
            'pengajuanIzinTerbaru'
        ));
    }
}
