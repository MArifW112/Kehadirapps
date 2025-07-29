<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Absensi;
use App\Models\JadwalKerja;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log; // Pastikan ini di-import

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
        $foto = $request->file('foto');
        $pathFoto = $foto->store('absen_faces', 'public'); // Foto disimpan di absen_faces di Persistent Volume

        // Dapatkan path fisik lengkap dari foto yang disimpan di Persistent Volume untuk script Python
        $fotoFull = Storage::disk('public')->path($pathFoto);

        Log::info('AbsenMasuk - Nama file: ' . $foto->getClientOriginalName());
        Log::info('AbsenMasuk - Path file di volume: ' . $fotoFull);
        // Log::info('AbsenMasuk - Ukuran file: ' . filesize($fotoFull)); // filesize() mungkin tidak akurat untuk storage driver selain 'local'

        // === DeepFace Validation ===
        $karyawanId = $request->karyawan_id;
        // Ubah path interpreter Python ke 'python3' untuk Linux
        $pythonExe = "python3";
        $predictScript = base_path('ai_face_recog/predict_deepface.py');

        // Ambil jalur root Persistent Volume dari variabel lingkungan Railway
        // Ini adalah path tempat folder 'face_db' berada
        $faceDbRootPath = env('RAILWAY_VOLUME_MOUNT_PATH');

        // Bangun perintah untuk Python, tambahkan $faceDbRootPath sebagai argumen terakhir
        $cmd = "\"$pythonExe\" \"$predictScript\" \"$fotoFull\" $karyawanId \"$faceDbRootPath\"";

        Log::info("[absenMasuk] CMD: $cmd");
        exec($cmd, $output, $exitCode);
        Log::info("[absenMasuk] predict_deepface.py OUTPUT:", $output);
        Log::info("[absenMasuk] predict_deepface.py Exit Code:", ['code' => $exitCode]);

        // Gabungkan hasil output python jadi satu string, lalu decode JSON
        $jsonOutput = implode("", $output);
        $deepface = json_decode($jsonOutput, true);

        // Tambahkan log jika decoding JSON gagal atau output tidak terduga
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error("[absenMasuk] Gagal decode JSON dari Python Output!", [
                'json_error' => json_last_error_msg(),
                'raw_output' => $output
            ]);
            // Sebagai fallback, jika DeepFace gagal atau output tidak valid, anggap tidak cocok
            $isMatch = false;
            $score = 0;
            $deepface = ['error' => 'Invalid DeepFace output or Python script error'];
        } else {
            $isMatch = isset($deepface['match']) && intval($deepface['match']) === 1;
            $score   = isset($deepface['score']) ? floatval($deepface['score']) : 0;
        }

        // THRESHOLD score (misal 0.50)
        $threshold = 0.50; // atau 0.7 sesuai uji lapangan
        if (!$isMatch || $score < $threshold) {
            Storage::disk('public')->delete($pathFoto); // Hapus foto yang baru diupload jika tidak match
            Log::warning("[absenMasuk] Wajah tidak sesuai/score terlalu rendah! score=$score threshold=$threshold, isMatch=" . ($isMatch ? 'true' : 'false'));
            return response()->json([
                'status'   => false,
                'message'  => 'Wajah tidak sesuai, silakan ulangi dengan foto yang jelas!',
                'deepface' => $deepface // info detail hasil python
            ], 422);
        }

        // Cek sudah absen hari ini?
        $absenToday = Absensi::where('karyawan_id', $karyawanId)
            ->where('tanggal', Carbon::today()->toDateString())
            ->first();

        if ($absenToday) {
            Storage::disk('public')->delete($pathFoto); // Hapus foto yang baru diupload
            Log::warning('[absenMasuk] User sudah absen masuk hari ini!');
            return response()->json(['status' => false, 'message' => 'Sudah absen masuk hari ini!'], 422);
        }

        $absensi = Absensi::create([
            'karyawan_id' => $karyawanId,
            'tanggal'     => Carbon::today()->toDateString(),
            'jam_masuk'   => $jamSekarang,
            'status'      => 'Hadir',
            'foto'        => $pathFoto, // simpan nama/path file foto (path relatif ke disk 'public')
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

        // Simpan foto yang diupload ke disk 'public' (yang kini adalah Persistent Volume)
        $foto = $request->file('foto');
        $pathFoto = $foto->store('absen_faces', 'public'); // Foto disimpan di absen_faces di Persistent Volume

        // Dapatkan path fisik lengkap dari foto yang disimpan di Persistent Volume untuk script Python
        $fotoFull = Storage::disk('public')->path($pathFoto);

        // === DeepFace Validation ===
        $karyawanId = $request->karyawan_id;
        // Ubah path interpreter Python ke 'python3' untuk Linux
        $pythonExe = "python3";
        $predictScript = base_path('ai_face_recog/predict_deepface.py');

        // Ambil jalur root Persistent Volume dari variabel lingkungan Railway
        $faceDbRootPath = env('RAILWAY_VOLUME_MOUNT_PATH');

        // Bangun perintah untuk Python, tambahkan $faceDbRootPath sebagai argumen terakhir
        $cmd = "\"$pythonExe\" \"$predictScript\" \"$fotoFull\" $karyawanId \"$faceDbRootPath\"";

        Log::info("[absenPulang] CMD: $cmd");
        exec($cmd, $output, $exitCode);
        Log::info("[absenPulang] predict_deepface.py OUTPUT:", $output);
        Log::info("[absenPulang] predict_deepface.py Exit Code:", ['code' => $exitCode]);

        // Gabungkan hasil output python jadi satu string, lalu decode JSON
        $jsonOutput = implode("", $output);
        $deepface = json_decode($jsonOutput, true);

        // Tambahkan log jika decoding JSON gagal atau output tidak terduga
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error("[absenPulang] Gagal decode JSON dari Python Output!", [
                'json_error' => json_last_error_msg(),
                'raw_output' => $output
            ]);
            $isMatch = false;
            $score = 0;
            $deepface = ['error' => 'Invalid DeepFace output or Python script error'];
        } else {
            $isMatch = isset($deepface['match']) && intval($deepface['match']) === 1;
            $score = isset($deepface['score']) ? floatval($deepface['score']) : 0;
        }

        $threshold = 0.50; // atau 0.7 sesuai uji lapangan

        if (!$isMatch || $score < $threshold) {
            Storage::disk('public')->delete($pathFoto); // Hapus foto yang baru diupload
            Log::warning("[absenPulang] Wajah tidak sesuai/score terlalu rendah (score=$score threshold=$threshold)!");
            return response()->json([
                'status'   => false,
                'message'  => 'Wajah tidak sesuai, silakan ulangi dengan foto yang jelas!',
                'deepface' => $deepface
            ], 422);
        }

        $absen = Absensi::where('karyawan_id', $karyawanId)
            ->where('tanggal', $request->tanggal)
            ->first();

        if (!$absen) {
            Storage::disk('public')->delete($pathFoto); // Hapus foto yang baru diupload
            Log::warning('[absenPulang] User belum absen masuk!');
            return response()->json(['status' => false, 'message' => 'Anda belum absen masuk!'], 422);
        }
        if ($absen->jam_pulang) {
            Storage::disk('public')->delete($pathFoto); // Hapus foto yang baru diupload
            Log::warning('[absenPulang] User sudah absen pulang hari ini!');
            return response()->json(['status' => false, 'message' => 'Anda sudah absen pulang hari ini!'], 422);
        }

        $absen->jam_pulang = $jamSekarang;
        $absen->save();

        Log::info('[absenPulang] Absen pulang berhasil!', ['absen' => $absen]);

        return response()->json([
            'status' => true,
            'message' => 'Absen pulang berhasil!',
            'deepface' => $deepface
        ]);
    }
}
