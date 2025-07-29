<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Karyawan;
use App\Models\Absensi;
use App\Models\JadwalKerja;
use App\Models\PengajuanIzin;
use Illuminate\Support\Carbon;

class AutoAlphaKaryawan extends Command
{
    protected $signature = 'absensi:auto-alpha';
    protected $description = 'Otomatis set status Alpha jika tidak absen dan tidak izin disetujui pada hari kerja';

    public function handle()
    {
        $hariIni = ucfirst(Carbon::now()->locale('id')->isoFormat('dddd'));
        $tanggal = Carbon::today()->toDateString();

        $jadwal = JadwalKerja::whereRaw('LOWER(hari) = ?', [strtolower($hariIni)])
            ->where('aktif', 1)
            ->first();

        if (!$jadwal) {
            $this->info("Hari ini bukan hari kerja, tidak ada proses auto alpha.");
            return;
        }

        $karyawans = Karyawan::all();
        $totalAlpha = 0;

        foreach ($karyawans as $karyawan) {
            $sudahAbsen = Absensi::where('karyawan_id', $karyawan->id)
                ->where('tanggal', $tanggal)
                ->exists();

            if ($sudahAbsen) continue;

            $izin = PengajuanIzin::where('karyawan_id', $karyawan->id)
                ->where('tanggal', $tanggal)
                ->where('status', 'Disetujui')
                ->exists();

            if ($izin) continue;

            Absensi::create([
                'karyawan_id' => $karyawan->id,
                'tanggal'     => $tanggal,
                'status'      => 'Alpha',
            ]);
            $totalAlpha++;
        }

        $this->info("Proses auto alpha selesai. Total karyawan Alpha hari ini: $totalAlpha");
    }
}
