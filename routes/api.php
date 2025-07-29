<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\PengajuanIzinApiController;
use App\Http\Controllers\Api\RiwayatApiController;
use App\Http\Controllers\Api\NotifikasiApiController;
use App\Http\Controllers\FaceRecognitionController;
use App\Http\Controllers\Api\LokasiKantorApiController;
use App\Http\Controllers\Api\AbsensiApiController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\StatistikApiController;
use App\Http\Controllers\Api\JadwalKerjaApiController;

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthApiController::class, 'api.auth.login']);
    Route::post('/register', [AuthApiController::class, 'register']);
    Route::post('/pengajuan-izin', [PengajuanIzinApiController::class, 'store']);
    Route::get('/riwayat-absensi', [RiwayatApiController::class, 'absensi']);
    Route::get('/riwayat-izin', [RiwayatApiController::class, 'izin']);
    Route::get('/notifikasi', [NotifikasiApiController::class, 'index']);
    Route::post('/notifikasi', [NotifikasiApiController::class, 'store']);
    Route::post('/notifikasi/mark-read', [NotifikasiApiController::class, 'markAsRead']);
    Route::post('/predict-face', [FaceRecognitionController::class, 'predict']);
    Route::get('/lokasi-kantor', [LokasiKantorApiController::class, 'index']);
    Route::post('/absen-masuk', [AbsensiApiController::class, 'absenMasuk']);
    Route::post('/absen-pulang', [AbsensiApiController::class, 'absenPulang']);
    Route::get('/statistik-absensi', [StatistikApiController::class, 'statistikAbsensi']);
    Route::post('/update-profile/{id}', [ProfileController::class, 'update']);
    Route::get('/jadwal-kerja-hari-ini', [JadwalKerjaApiController::class, 'hariIni']);
});
