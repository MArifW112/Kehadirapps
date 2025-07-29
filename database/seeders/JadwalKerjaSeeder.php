<?php
namespace Database\Seeders; // <-- PENTING BANGET

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JadwalKerjaSeeder extends Seeder
{
    public function run()
    {
        $days = [
            ['hari' => 'Senin',    'jam_masuk' => '08:00:00', 'jam_pulang' => '17:00:00', 'aktif' => 1],
            ['hari' => 'Selasa',   'jam_masuk' => '08:00:00', 'jam_pulang' => '17:00:00', 'aktif' => 1],
            ['hari' => 'Rabu',     'jam_masuk' => '08:00:00', 'jam_pulang' => '17:00:00', 'aktif' => 1],
            ['hari' => 'Kamis',    'jam_masuk' => '08:00:00', 'jam_pulang' => '17:00:00', 'aktif' => 1],
            ['hari' => 'Jumat',    'jam_masuk' => '08:00:00', 'jam_pulang' => '17:00:00', 'aktif' => 1],
            ['hari' => 'Sabtu',    'jam_masuk' => null,       'jam_pulang' => null,        'aktif' => 0],
            ['hari' => 'Minggu',   'jam_masuk' => null,       'jam_pulang' => null,        'aktif' => 0],
        ];
        DB::table('jadwal_kerja')->insert($days);
    }
}
