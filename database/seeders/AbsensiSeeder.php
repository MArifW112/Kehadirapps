<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Absensi;
use App\Models\Karyawan;
use Illuminate\Support\Carbon;

class AbsensiSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = ['Hadir', 'Izin', 'Alpha'];
        $jamMasukOptions = ['07:50:00', '08:00:00', '08:15:00', '08:30:00'];
        $jamPulang = '16:00:00';

        $karyawans = Karyawan::all();

        foreach ($karyawans as $karyawan) {
            for ($i = 0; $i < 7; $i++) {
                $tanggal = Carbon::now()->subDays($i)->toDateString();

                Absensi::updateOrCreate([
                    'karyawan_id' => $karyawan->id,
                    'tanggal' => $tanggal,
                ], [
                    'jam_masuk' => $jamMasukOptions[array_rand($jamMasukOptions)],
                    'jam_pulang' => $jamPulang,
                    'status' => $statuses[array_rand($statuses)],
                ]);
            }
        }
    }
}
