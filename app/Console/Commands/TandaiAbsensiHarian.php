<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Karyawan;
use App\Models\Absensi;
use App\Models\JadwalKerja;
use App\Models\PengajuanIzin;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log; // Penting untuk melihat log di Railway

class TandaiAbsensiHarian extends Command
{
    /**
     * Nama dan tanda tangan (signature) perintah konsol.
     *
     * @var string
     */
    protected $signature = 'absensi:tandai-alpha {--tanggal= : Tanggal spesifik untuk menandai absensi (YYYY-MM-DD)}';

    /**
     * Deskripsi perintah konsol.
     *
     * @var string
     */
    protected $description = 'Menandai karyawan sebagai "Alpha" jika belum absen melewati batas waktu.';

    /**
     * Jalankan perintah konsol.
     */
    public function handle()
    {
        // Ambil tanggal dari argumen (--tanggal) atau default hari ini
        $tanggal = $this->option('tanggal') ?? Carbon::today()->toDateString();
        $namaHari = ucfirst(Carbon::parse($tanggal)->locale('id')->isoFormat('dddd'));

        $this->info("Menjalankan penandaan absensi untuk tanggal: {$tanggal}");
        Log::info("Scheduler: Menjalankan penandaan absensi untuk tanggal: {$tanggal}");

        // Cari jadwal kerja aktif hari itu
        $jadwal = JadwalKerja::whereRaw('LOWER(hari) = ?', [strtolower($namaHari)])
            ->where('aktif', 1)
            ->first();

        // ===== Pengecekan Hari Libur =====
        if (!$jadwal) {
            $this->info("Hari ini ({$namaHari}) adalah hari libur atau tidak ada jadwal aktif. Melewatkan penandaan absensi.");
            Log::info("Scheduler: Hari ini ({$namaHari}) adalah hari libur atau tidak ada jadwal aktif. Melewatkan penandaan absensi.");
            return Command::SUCCESS;
        }

        // ===== HARI KERJA (ada jadwal aktif) =====
        // Ambil semua karyawan yang sudah aktif sebelum/sama dengan tanggal yang difilter
        $karyawans = Karyawan::whereDate('created_at', '<=', $tanggal)->get();

        $jumlahDitandai = 0;
        foreach ($karyawans as $karyawan) {
            // Cek apakah karyawan sudah absen masuk pada tanggal tersebut
            $absen = Absensi::where('karyawan_id', $karyawan->id)
                ->where('tanggal', $tanggal)
                ->whereNotNull('jam_masuk') // Pastikan sudah absen masuk
                ->first();

            // Cek apakah karyawan memiliki pengajuan izin yang disetujui pada tanggal tersebut
            $izin = PengajuanIzin::where('karyawan_id', $karyawan->id)
                ->where('tanggal', $tanggal)
                ->where('status', 'Disetujui')
                ->first();

            // Jika tidak ada catatan absen masuk DAN tidak ada izin yang disetujui
            if (!$absen && !$izin) {
                $waktuSaatIni = Carbon::now()->format('H:i:s');
                // Batas waktu untuk dianggap Alpha (misal, 1 jam setelah jam masuk jadwal)
                $batasWaktuAlpha = Carbon::parse($jadwal->jam_masuk)->addHour()->format('H:i:s');

                // Jika sudah melewati batas waktu Alpha ATAU tanggal yang diproses adalah hari sebelumnya
                if ($waktuSaatIni >= $batasWaktuAlpha || Carbon::parse($tanggal)->lt(Carbon::today())) {
                    // Cek apakah sudah ada catatan absensi untuk hari itu (mungkin statusnya belum Alpha)
                    $absensiTersimpan = Absensi::where('karyawan_id', $karyawan->id)
                        ->where('tanggal', $tanggal)
                        ->first();

                    // Jika belum ada catatan absensi, buat yang baru dengan status Alpha
                    if (!$absensiTersimpan) {
                        Absensi::create([
                            'karyawan_id' => $karyawan->id,
                            'tanggal' => $tanggal,
                            'status' => 'Alpha',
                            'jam_masuk' => null,
                            'jam_pulang' => null,
                            'foto' => null,
                        ]);
                        $jumlahDitandai++;
                        $this->info("Karyawan '{$karyawan->nama_karyawan}' ditandai sebagai Alpha untuk {$tanggal}.");
                        Log::info("Scheduler: Karyawan '{$karyawan->nama_karyawan}' ditandai sebagai Alpha untuk {$tanggal}.");
                    }
                    // Jika sudah ada catatan absensi tapi statusnya bukan Alpha dan belum absen masuk, update menjadi Alpha
                    else if ($absensiTersimpan->status !== 'Alpha' && empty($absensiTersimpan->jam_masuk)) {
                         $absensiTersimpan->update(['status' => 'Alpha']);
                         $jumlahDitandai++;
                         $this->info("Karyawan '{$karyawan->nama_karyawan}' catatan absensi diperbarui menjadi Alpha untuk {$tanggal}.");
                         Log::info("Scheduler: Karyawan '{$karyawan->nama_karyawan}' catatan absensi diperbarui menjadi Alpha untuk {$tanggal}.");
                    }
                }
            }
        }

        $this->info("Selesai menandai absensi. Total karyawan ditandai Alpha: {$jumlahDitandai}");
        Log::info("Scheduler: Selesai menandai absensi. Total karyawan ditandai Alpha: {$jumlahDitandai}");

        return Command::SUCCESS;
    }
}
