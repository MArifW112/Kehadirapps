<?php

namespace Database\Seeders;

use App\Models\PengajuanIzin;
use App\Models\Karyawan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PengajuanIzinSeeder extends Seeder
{
    public function run()
{
    $karyawan = Karyawan::inRandomOrder()->first();

    PengajuanIzin::create([
        'karyawan_id' => $karyawan->id,
        'jenis' => 'Sakit',
        'alasan' => 'Demam tinggi dan tidak memungkinkan untuk bekerja.',
        'tanggal' => now()->toDateString(),
        'foto_bukti' => 'izin/surat_sakit_dummy.jpg', // pastikan file ada di storage/app/public/izin
        'status' => 'menunggu',
    ]);
}
}