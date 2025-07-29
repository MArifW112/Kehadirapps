<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardAdminController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\RiwayatAbsensiController;
use App\Http\Controllers\PengajuanIzinController;
use Illuminate\Notifications\DatabaseNotification;
use App\Http\Controllers\LokasiKantorController;
use App\Http\Controllers\JadwalKerjaController;
use App\Http\Controllers\NotifikasiAdminController;
use App\Http\Controllers\Admin\PengaturanController;
use App\Http\Middleware\RoleAdmin;



// Redirect default ke login
Route::get('/', function () {
    return view('auth.login');
})->name('login.show');
// Grup route khusus admin
Route::prefix('admin')
->middleware([\App\Http\Middleware\RoleAdmin::class])
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [DashboardAdminController::class, 'index'])->name('dashboard');
        Route::get('/grafik-absensi', [DashboardAdminController::class, 'grafikAbsensi'])->name('grafik-absensi');
        // === Retrain AI ===
        Route::post('/retrain-ai', [KaryawanController::class, 'retrainAI'])->name('retrain-ai');

        // Manajemen Karyawan
        Route::resource('karyawan', KaryawanController::class);
        Route::delete('/karyawan/foto/{id}', [KaryawanController::class, 'deleteFoto'])->name('karyawan.foto.destroy');

        // Riwayat Absensi
        Route::get('/riwayat-absensi', [RiwayatAbsensiController::class, 'index'])->name('riwayat.absensi.index');

        // Pengajuan Izin
        Route::get('/pengajuan-izin', [PengajuanIzinController::class, 'index'])->name('pengajuan_izin.index');
        Route::get('/pengajuan-izin/{id}', [PengajuanIzinController::class, 'show'])->name('pengajuan_izin.show');
        Route::delete('/pengajuan-izin/{id}', [PengajuanIzinController::class, 'destroy'])->name('pengajuan_izin.destroy');
        Route::patch('/pengajuan-izin/{id}', [PengajuanIzinController::class, 'update'])->name('pengajuan_izin.update');

        // Lokasi Kantor
        Route::get('/lokasi-kantor', [LokasiKantorController::class, 'index'])->name('lokasi-kantor.index');
        Route::post('/lokasi-kantor', [LokasiKantorController::class, 'update'])->name('lokasi-kantor.update');

        // Notifikasi
        Route::get('/notifikasi', [NotifikasiAdminController::class, 'index'])->name('notifikasi');
        Route::get('/notifikasi/popup', [NotifikasiAdminController::class, 'popup'])->name('notifikasi.popup');
        Route::post('/notifikasi/baca', [NotifikasiAdminController::class, 'markAsRead'])->name('notifikasi.baca');

        Route::get('/jadwal-kerja', [JadwalKerjaController::class, 'index'])->name('jadwal-kerja.index');
        Route::patch('/jadwal-kerja/{id}', [JadwalKerjaController::class, 'update'])->name('jadwal-kerja.update');

        // Pengaturan Admin
        Route::get('/pengaturan', function() {return view('admin.pengaturan.index');})->name('pengaturan.index');
        // Ganti password admin
        Route::get('/pengaturan/ganti-password', [PengaturanController::class, 'gantiPasswordForm'])->name('pengaturan.ganti-password');
        Route::patch('/pengaturan/ganti-password', [PengaturanController::class, 'gantiPasswordUpdate'])->name('pengaturan.ganti-password.update');
        Route::get('/pengaturan/export-karyawan', [PengaturanController::class, 'exportKaryawan'])->name('pengaturan.export_karyawan');
        Route::get('/pengaturan/export-absensi', [PengaturanController::class, 'exportAbsensi'])->name('pengaturan.export_absensi');    });

    // Profil (masih dibatasi admin)
    Route::middleware(['auth', RoleAdmin::class])->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });

// Auth routes dari Breeze
require __DIR__.'/auth.php';
