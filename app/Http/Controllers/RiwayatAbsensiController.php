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
        $namaHari = ucfirst(Carbon::parse($tanggal)->locale('id')->isoFormat('dddd'));

        // Cari jadwal kerja aktif hari itu
        $jadwal = JadwalKerja::whereRaw('LOWER(hari) = ?', [strtolower($namaHari)])
            ->where('aktif', 1)
            ->first();

        $absensis = [];

        // ===== Pengecekan Hari Libur =====
        if (!$jadwal) {
            // Jika tidak ada jadwal aktif (Hari Libur), semua dianggap libur
            $karyawans = Karyawan::whereDate('created_at', '<=', $tanggal)->get();
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
        } else {
            // ===== HARI KERJA (ada jadwal aktif) =====
            // Ambil karyawan yang sudah aktif sebelum/sama tanggal filter
            $karyawans = Karyawan::whereDate('created_at', '<=', $tanggal)->get();

            foreach ($karyawans as $karyawan) {
                // Ambil catatan absensi karyawan untuk tanggal tersebut
                $absen = Absensi::where('karyawan_id', $karyawan->id)
                    ->where('tanggal', $tanggal)
                    ->first();

                // Ambil pengajuan izin yang disetujui untuk karyawan pada tanggal tersebut
                $izin = PengajuanIzin::where('karyawan_id', $karyawan->id)
                    ->where('tanggal', $tanggal)
                    ->where('status', 'Disetujui')
                    ->first();

                $statusUntukTampilan = '';
                $jamMasukTampilan = '-';
                $jamPulangTampilan = '-';
                $fotoTampilan = null;

                // Tentukan status berdasarkan data yang ada di database atau perhitungan sementara untuk tampilan
                if ($izin) {
                    $statusUntukTampilan = 'Izin';
                } elseif ($absen) {
                    // Jika ada data absen di DB
                    $jamMasukTampilan = $absen->jam_masuk ?? '-';
                    $jamPulangTampilan = $absen->jam_pulang ?? '-';
                    $fotoTampilan = $absen->foto ?? null;

                    if ($absen->status) {
                        $statusUntukTampilan = $absen->status; // Ambil status langsung dari DB (Hadir, Telat, Alpha)
                    } elseif ($absen->jam_masuk) {
                        // Jika jam masuk ada tapi status belum diisi (jarang terjadi setelah scheduler)
                        if ($absen->jam_masuk > Carbon::parse($jadwal->jam_masuk)->addMinutes(30)->format('H:i:s')) {
                            $statusUntukTampilan = 'Telat';
                        } else {
                            $statusUntukTampilan = 'Hadir';
                        }
                    } else {
                        // Ini kasus jika ada record tapi jam_masuk null (kemungkinan record Alpha dari scheduler)
                        $statusUntukTampilan = 'Alpha';
                    }
                } else {
                    // Jika tidak ada data absen DAN tidak ada izin di DB
                    $waktuSaatIni = Carbon::now()->format('H:i:s');
                    $batasWaktuAlpha = Carbon::parse($jadwal->jam_masuk)->addHour()->format('H:i:s');

                    if ($waktuSaatIni >= $batasWaktuAlpha || Carbon::parse($tanggal)->lt(Carbon::today())) {
                        // Jika sudah melewati deadline atau ini hari sebelumnya, asumsikan Alpha untuk tampilan
                        // (scheduler yang akan menyimpan ini ke DB)
                        $statusUntukTampilan = 'Alpha'; // Akan di-override jika scheduler sudah jalan
                    } else {
                        // Belum melewati deadline untuk hari ini
                        $statusUntukTampilan = 'Belum Absen';
                    }
                }

                $absensis[] = [
                    'nama_karyawan' => $karyawan->nama_karyawan,
                    'tanggal'       => $tanggal,
                    'jam_masuk'     => $jamMasukTampilan,
                    'foto'          => $fotoTampilan,
                    'jam_pulang'    => $jamPulangTampilan,
                    'status'        => $statusUntukTampilan,
                ];
            }
        }

        return view('admin.riwayat_absensi.index', compact('absensis', 'tanggal'));
    }
}
