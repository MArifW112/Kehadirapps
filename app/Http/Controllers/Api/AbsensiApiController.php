<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Absensi;
use App\Models\JadwalKerja;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;

class AbsensiApiController extends Controller
{
    public function absenMasuk(Request $request)
    {
        Log::info('==== [absenMasuk] Diterima Request ====');
        Log::info('Request fields:', $request->all());

        $request->validate([
            'karyawan_id' => 'required|integer',
            'lokasi'      => 'required|string',
            'foto'        => 'required|image|max:4096',
        ]);

        $hariIni = ucfirst(Carbon::now()->locale('id')->isoFormat('dddd'));
        $jamSekarang = Carbon::now()->format('H:i:s');
        Log::info('[absenMasuk] Hari sekarang: ' . $hariIni . ', Jam sekarang: ' . $jamSekarang);

        $jadwal = JadwalKerja::whereRaw('LOWER(hari) = ?', [strtolower($hariIni)])->first();
        Log::info('[absenMasuk] Hasil query jadwal_kerja:', ['query_hari' => $hariIni, 'jadwal' => $jadwal]);

        if (!$jadwal) {
            Log::warning('[absenMasuk] Tidak ada jadwal kerja untuk hari ini!');
            return response()->json(['status' => false, 'message' => 'Hari ini libur, tidak bisa absen!'], 422);
        }
        if (!$jadwal->aktif) {
            Log::warning('[absenMasuk] Jadwal kerja hari ini tidak aktif!');
            return response()->json(['status' => false, 'message' => 'Hari ini libur, tidak bisa absen!'], 422);
        }
        if ($jamSekarang < $jadwal->jam_masuk || $jamSekarang > $jadwal->jam_pulang) {
            Log::warning("[absenMasuk] Diluar jam kerja! (jam sekarang: $jamSekarang, jam masuk: $jadwal->jam_masuk, jam pulang: $jadwal->jam_pulang)");
            return response()->json(['status' => false, 'message' => 'Absensi hanya boleh dilakukan pada jam kerja!'], 422);
        }

        // Simpan foto yang diupload ke disk 'public' (yang kini adalah Persistent Volume)
        $foto      = $request->file('foto');
        // Simpan foto asli sementara di lokasi temporer Laravel (disk 'private')
        $tempPath  = $foto->store('temp_uploads', 'private');
        $tempFullPath = Storage::disk('private')->path($tempPath);

        Log::info('AbsenMasuk - Nama file: ' . $foto->getClientOriginalName());
        Log::info('AbsenMasuk - Path file temporer: ' . $tempFullPath);

        // === Panggilan ke API Python untuk DeepFace Validation ===
        $karyawanId = $request->karyawan_id;
        // Ambil URL API Python dari variabel lingkungan Railway
        $pythonApiUrl = env('PYTHON_API_URL');

        // Pastikan URL API Python ada
        if (empty($pythonApiUrl)) {
            Log::error('[absenMasuk] PYTHON_API_URL tidak terdefinisi di .env Laravel!');
            Storage::disk('private')->delete($tempPath); // Hapus foto temporer
            return response()->json(['status' => false, 'message' => 'Konfigurasi server tidak lengkap (API Python URL missing).'], 500);
        }

        $client = new Client(); // Inisialisasi Guzzle HTTP client
        $deepface = null; // Inisialisasi hasil DeepFace

        try {
            $response = $client->post("$pythonApiUrl/predict-face", [
                'multipart' => [ // Kirim sebagai multipart form-data untuk file dan field lainnya
                    [
                        'name'     => 'foto',
                        'contents' => fopen($tempFullPath, 'r'), // Buka file untuk dikirim
                        'filename' => basename($tempFullPath) // Nama file asli
                    ],
                    [
                        'name'     => 'karyawan_id',
                        'contents' => $karyawanId
                    ],
                    [
                        'name'     => 'threshold',
                        'contents' => 0.50 // Kirim threshold jika Anda ingin menyesuaikannya
                    ]
                ],
                'verify' => false // Opsional: Nonaktifkan verifikasi SSL untuk development jika ada masalah sertifikat
            ]);

            $statusCode = $response->getStatusCode();
            $body = $response->getBody()->getContents();
            $deepface = json_decode($body, true);

            Log::info("[absenMasuk] API Python Response Status: $statusCode");
            Log::info("[absenMasuk] API Python Response Body:", ['body' => $deepface]);

            // Periksa jika API Python mengembalikan error HTTP (status code >= 400) atau format JSON tidak sesuai
            if ($statusCode >= 400 || !is_array($deepface) || !isset($deepface['match'])) {
                Log::warning("[absenMasuk] API Python mengembalikan error atau format tidak sesuai: Status $statusCode, Body: $body");
                return response()->json([
                    'status' => false,
                    'message' => 'Terjadi kesalahan pada layanan verifikasi wajah.',
                    'deepface_raw' => $deepface // Kirim raw response jika ada masalah parsing
                ], 500);
            }

        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $responseBody = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : $e->getMessage();
            Log::error("[absenMasuk] Guzzle RequestException: " . $e->getMessage(), ['response' => $responseBody]);
            // Coba decode body jika ada untuk deepface['error']
            $errorResponse = json_decode($responseBody, true);
            return response()->json([
                'status' => false,
                'message' => 'Gagal terhubung ke layanan verifikasi wajah: ' . ($errorResponse['error'] ?? 'Internal Server Error'),
                'deepface_error' => $errorResponse
            ], 500);
        } catch (\Exception $e) {
            Log::error("[absenMasuk] General Exception calling Python API: " . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan tak terduga saat verifikasi wajah.',
                'error' => $e->getMessage()
            ], 500);
        } finally {
            Storage::disk('private')->delete($tempPath); // Hapus foto temporer setelah diproses
            Log::info("[absenMasuk] File temporer dihapus: $tempFullPath");
        }

        $isMatch = isset($deepface['match']) && intval($deepface['match']) === 1;
        $score   = isset($deepface['score']) ? floatval($deepface['score']) : 0;

        $threshold = 0.50; // atau 0.7 sesuai uji lapangan
        if (!$isMatch || $score < $threshold) {
            Log::warning("[absenMasuk] Wajah tidak sesuai/score terlalu rendah! score=$score threshold=$threshold, isMatch=" . ($isMatch ? 'true' : 'false'));
            return response()->json([
                'status'   => false,
                'message'  => 'Wajah tidak sesuai, silakan ulangi dengan foto yang jelas!',
                'deepface' => $deepface // info detail hasil python
            ], 422);
        }

        // Simpan foto asli yang diupload ke disk 'public' (Persistent Volume) setelah DeepFace Match
        // Ini adalah foto yang akan disimpan di DB sebagai bukti absensi
        $finalFotoPath = $foto->store('absen_faces', 'public');

        // Cek sudah absen hari ini?
        $absenToday = Absensi::where('karyawan_id', $karyawanId)
            ->where('tanggal', Carbon::today()->toDateString())
            ->first();

        if ($absenToday) {
            Storage::disk('public')->delete($finalFotoPath); // Hapus foto jika sudah absen
            Log::warning('[absenMasuk] User sudah absen masuk hari ini!');
            return response()->json(['status' => false, 'message' => 'Sudah absen masuk hari ini!'], 422);
        }

        $absensi = Absensi::create([
            'karyawan_id' => $karyawanId,
            'tanggal'     => Carbon::today()->toDateString(),
            'jam_masuk'   => $jamSekarang,
            'status'      => 'Hadir',
            'foto'        => $finalFotoPath, // simpan nama/path file foto
        ]);

        Log::info('[absenMasuk] Absen berhasil disimpan!', ['absensi' => $absensi]);

        return response()->json([
            'status'   => true,
            'message'  => 'Absen berhasil disimpan!',
            'data'     => $absensi,
            'deepface' => $deepface // info hasil match
        ]);
    }

    public function absenPulang(Request $request)
    {
        Log::info('==== [absenPulang] Diterima Request ====');
        Log::info('Request fields:', $request->all());

        $request->validate([
            'karyawan_id' => 'required|integer',
            'tanggal'     => 'required|date',
            'jam_pulang'  => 'required',
            'foto'        => 'required|image|max:4096',
            'lokasi'      => 'required|string'
        ]);

        $hariIni = ucfirst(Carbon::now()->locale('id')->isoFormat('dddd'));
        $jamSekarang = Carbon::now()->format('H:i:s');
        Log::info('[absenPulang] Hari sekarang: ' . $hariIni . ', Jam sekarang: ' . $jamSekarang);

        $jadwal = JadwalKerja::whereRaw('LOWER(hari) = ?', [strtolower($hariIni)])->first();
        Log::info('[absenPulang] Hasil query jadwal_kerja:', ['query_hari' => $hariIni, 'jadwal' => $jadwal]);

        if (!$jadwal) {
            Log::warning('[absenPulang] Tidak ada jadwal kerja untuk hari ini!');
            return response()->json(['status' => false, 'message' => 'Hari ini libur, tidak bisa absen!'], 422);
        }
        if (!$jadwal->aktif) {
            Log::warning('[absenPulang] Jadwal kerja hari ini tidak aktif!');
            return response()->json(['status' => false, 'message' => 'Hari ini libur, tidak bisa absen!'], 422);
        }
        if ($jamSekarang < $jadwal->jam_pulang) {
            Log::warning("[absenPulang] Belum waktunya pulang! (jam sekarang: $jamSekarang, jam pulang: $jadwal->jam_pulang)");
            return response()->json(['status' => false, 'message' => 'Absen pulang hanya bisa dilakukan setelah jam pulang!'], 422);
        }

        // Simpan foto asli sementara di lokasi temporer Laravel (disk 'private')
        $foto      = $request->file('foto');
        $tempPath  = $foto->store('temp_uploads', 'private');
        $tempFullPath = Storage::disk('private')->path($tempPath);

        Log::info('AbsenPulang - Nama file: ' . $foto->getClientOriginalName());
        Log::info('AbsenPulang - Path file temporer: ' . $tempFullPath);

        // === Panggilan ke API Python untuk DeepFace Validation ===
        $karyawanId = $request->karyawan_id;
        $pythonApiUrl = env('PYTHON_API_URL'); // Ambil URL API Python dari variabel lingkungan

        if (empty($pythonApiUrl)) {
            Log::error('[absenPulang] PYTHON_API_URL tidak terdefinisi di .env Laravel!');
            Storage::disk('private')->delete($tempPath); // Hapus foto temporer
            return response()->json(['status' => false, 'message' => 'Konfigurasi server tidak lengkap.'], 500);
        }

        $client = new Client();
        $deepface = null;

        try {
            $response = $client->post("$pythonApiUrl/predict-face", [
                'multipart' => [
                    [
                        'name'     => 'foto',
                        'contents' => fopen($tempFullPath, 'r'),
                        'filename' => basename($tempFullPath)
                    ],
                    [
                        'name'     => 'karyawan_id',
                        'contents' => $karyawanId
                    ],
                    [
                        'name'     => 'threshold',
                        'contents' => 0.50
                    ]
                ],
                'verify' => false // Opsional: Nonaktifkan verifikasi SSL untuk development jika ada masalah sertifikat
            ]);

            $statusCode = $response->getStatusCode();
            $body = $response->getBody()->getContents();
            $deepface = json_decode($body, true);

            Log::info("[absenPulang] API Python Response Status: $statusCode");
            Log::info("[absenPulang] API Python Response Body:", ['body' => $deepface]);

            // Periksa jika API Python mengembalikan error HTTP (status code >= 400) atau format JSON tidak sesuai
            if ($statusCode >= 400 || !is_array($deepface) || !isset($deepface['match'])) {
                Log::warning("[absenPulang] API Python mengembalikan error atau format tidak sesuai: Status $statusCode, Body: $body");
                return response()->json([
                    'status' => false,
                    'message' => 'Terjadi kesalahan pada layanan verifikasi wajah.',
                    'deepface_raw' => $deepface
                ], 500);
            }

        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $responseBody = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : $e->getMessage();
            Log::error("[absenPulang] Guzzle RequestException: " . $e->getMessage(), ['response' => $responseBody]);
            $errorResponse = json_decode($responseBody, true);
            return response()->json([
                'status' => false,
                'message' => 'Gagal terhubung ke layanan verifikasi wajah: ' . ($errorResponse['error'] ?? 'Internal Server Error'),
                'deepface_error' => $errorResponse
            ], 500);
        } catch (\Exception $e) {
            Log::error("[absenPulang] General Exception calling Python API: " . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan tak terduga saat verifikasi wajah.',
                'error' => $e->getMessage()
            ], 500);
        } finally {
            Storage::disk('private')->delete($tempPath); // Hapus foto temporer setelah diproses
            Log::info("[absenPulang] File temporer dihapus: $tempFullPath"); // Log penghapusan di finally
        }

        $isMatch = isset($deepface['match']) && intval($deepface['match']) === 1;
        $score = isset($deepface['score']) ? floatval($deepface['score']) : 0;
        $threshold = 0.50; // atau 0.7 sesuai uji lapangan

        if (!$isMatch || $score < $threshold) {
            // Foto temporer sudah dihapus di blok try-catch-finally
            Log::warning("[absenPulang] Wajah tidak sesuai/score terlalu rendah (score=$score threshold=$threshold)!");
            return response()->json([
                'status'   => false,
                'message'  => 'Wajah tidak sesuai, silakan ulangi dengan foto yang jelas!',
                'deepface' => $deepface
            ], 422);
        }

        // Simpan foto asli yang diupload ke disk 'public' (Persistent Volume) setelah DeepFace Match
        // Ini adalah foto yang akan disimpan di DB sebagai bukti absensi
        $finalFotoPath = $foto->store('absen_faces', 'public');

        $absen = Absensi::where('karyawan_id', $karyawanId)
            ->where('tanggal', $request->tanggal)
            ->first();

        if (!$absen) {
            Storage::disk('public')->delete($finalFotoPath); // Hapus foto jika belum absen masuk
            Log::warning('[absenPulang] User belum absen masuk!');
            return response()->json(['status' => false, 'message' => 'Anda belum absen masuk!'], 422);
        }
        if ($absen->jam_pulang) {
            Storage::disk('public')->delete($finalFotoPath); // Hapus foto jika sudah absen pulang
            Log::warning('[absenPulang] User sudah absen pulang hari ini!');
            return response()->json(['status' => false, 'message' => 'Anda sudah absen pulang hari ini!'], 422);
        }

        $absen->jam_pulang = $jamSekarang;
        $absen->save();

        Log::info('[absenPulang] Absen pulang berhasil!', ['absen' => $absen]);

        return response()->json([
            'status' => true,
            'message' => 'Absen pulang berhasil!',
            'deepface' => $deepface,
            'data'     => $absen // Tambahkan data absen yang diupdate
        ]);
    }
}
