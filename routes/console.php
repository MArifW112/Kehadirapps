<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Console\Commands\TandaiAbsensiHarian;


Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Menampilkan kutipan inspiratif');

// --- DAFTARKAN DAN JADWALKAN PERINTAH ABSENSI DI SINI ---
Artisan::command('absensi:jalankan-penandaan', function () {
    // Panggil perintah TandaiAbsensiHarian yang sudah Anda buat
    // Gunakan call (bukan new TandaiAbsensiHarian()->handle()) agar dihandle oleh Laravel
    $this->call(TandaiAbsensiHarian::class);

    $this->info('Perintah penandaan absensi otomatis telah dijalankan.');
})->purpose('Menjalankan proses penandaan absensi Alpha secara otomatis setiap hari.')
->dailyAt('18:40'); // UBAH JADWAL DI SINI UNTUK TESTING
