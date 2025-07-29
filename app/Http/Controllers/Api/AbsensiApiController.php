<?php

    namespace App\Http\Controllers\Api;

    use App\Http\Controllers\Controller;
    use Illuminate\Http\Request;
    use App\Models\Absensi;
    use App\Models\JadwalKerja;
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Facades\Log;

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

            $foto      = $request->file('foto');
            $pathFoto  = $foto->store('absen_faces', 'public');
            $fotoFull  = storage_path('app/public/' . $pathFoto);

            Log::info('AbsenMasuk - Nama file: ' . $foto->getClientOriginalName());
            Log::info('AbsenMasuk - Path file: ' . $fotoFull);
            Log::info('AbsenMasuk - Ukuran file: ' . filesize($fotoFull));

            // === DeepFace Validation ===
            $karyawanId = $request->karyawan_id;
            $pythonExe = "C:\\Users\\LENOVO\\AppData\\Local\\Programs\\Python\\Python312\\python.exe";
            $predictScript = base_path('ai_face_recog/predict_deepface.py');
            $cmd = "\"$pythonExe\" \"$predictScript\" \"$fotoFull\" $karyawanId";

            Log::info("[absenMasuk] CMD: $cmd");
            exec($cmd, $output, $exitCode);
            Log::info("[absenMasuk] predict_deepface.py OUTPUT:", $output);

            // Gabungkan hasil output python jadi satu string, lalu decode JSON
            $jsonOutput = implode("", $output);
            $deepface = json_decode($jsonOutput, true);

            $isMatch = isset($deepface['match']) && intval($deepface['match']) === 1;
            $score   = isset($deepface['score']) ? floatval($deepface['score']) : 0;

            // THRESHOLD score (misal 0.70)
            $threshold = 0.50;
            if (!$isMatch || $score < $threshold) {
                Storage::disk('public')->delete($pathFoto);
                Log::warning("[absenMasuk] Wajah tidak sesuai/score terlalu rendah! score=$score threshold=$threshold");
                return response()->json([
                    'status'  => false,
                    'message' => 'Wajah tidak sesuai, silakan ulangi dengan foto yang jelas!',
                    'deepface' => $deepface // info detail hasil python
                ], 422);
            }

            // Cek sudah absen hari ini?
            $absenToday = Absensi::where('karyawan_id', $karyawanId)
                ->where('tanggal', Carbon::today()->toDateString())
                ->first();

            if ($absenToday) {
                Storage::disk('public')->delete($pathFoto);
                Log::warning('[absenMasuk] User sudah absen masuk hari ini!');
                return response()->json(['status' => false, 'message' => 'Sudah absen masuk hari ini!'], 422);
            }

            $absensi = Absensi::create([
                'karyawan_id' => $karyawanId,
                'tanggal'     => Carbon::today()->toDateString(),
                'jam_masuk'   => $jamSekarang,
                'status'      => 'Hadir',
                'foto'        => $pathFoto, // simpan nama/path file foto
            ]);

            // Storage::disk('public')->delete($pathFoto);

            Log::info('[absenMasuk] Absen berhasil disimpan!', ['absensi' => $absensi]);

            return response()->json([
                'status'  => true,
                'message' => 'Absen berhasil disimpan!',
                'data'    => $absensi,
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

            $foto      = $request->file('foto');
            $pathFoto  = $foto->store('absen_faces', 'public');
            $fotoFull  = storage_path('app/public/' . $pathFoto);

            // === DeepFace Validation ===
            $karyawanId = $request->karyawan_id;
            $pythonExe = "C:\\Users\\LENOVO\\AppData\\Local\\Programs\\Python\\Python312\\python.exe";
            $predictScript = base_path('ai_face_recog/predict_deepface.py');
            $cmd = "\"$pythonExe\" \"$predictScript\" \"$fotoFull\" $karyawanId";

            Log::info("[absenPulang] CMD: $cmd");
            exec($cmd, $output, $exitCode);
            Log::info("[absenPulang] predict_deepface.py OUTPUT:", $output);

            // Gabungkan hasil output python jadi satu string, lalu decode JSON
            $jsonOutput = implode("", $output);
            $deepface = json_decode($jsonOutput, true);

            $isMatch = isset($deepface['match']) && intval($deepface['match']) === 1;
            $score = isset($deepface['score']) ? floatval($deepface['score']) : 0;
            $threshold = 0.50; // atau 0.7 sesuai uji lapangan

            if (!$isMatch || $score < $threshold) {
                Storage::disk('public')->delete($pathFoto);
                Log::warning("[absenPulang] Wajah tidak sesuai/score terlalu rendah (score=$score threshold=$threshold)!");
                return response()->json([
                    'status'  => false,
                    'message' => 'Wajah tidak sesuai, silakan ulangi dengan foto yang jelas!',
                    'deepface' => $deepface
                ], 422);
            }
            $absen = Absensi::where('karyawan_id', $karyawanId)
                ->where('tanggal', $request->tanggal)
                ->first();

            if (!$absen) {
                Storage::disk('public')->delete($pathFoto);
                Log::warning('[absenPulang] User belum absen masuk!');
                return response()->json(['status' => false, 'message' => 'Anda belum absen masuk!'], 422);
            }
            if ($absen->jam_pulang) {
                Storage::disk('public')->delete($pathFoto);
                Log::warning('[absenPulang] User sudah absen pulang hari ini!');
                return response()->json(['status' => false, 'message' => 'Anda sudah absen pulang hari ini!'], 422);
            }

            $absen->jam_pulang = $jamSekarang;
            $absen->save();

            // Storage::disk('public')->delete($pathFoto);

            Log::info('[absenPulang] Absen pulang berhasil!', ['absen' => $absen]);

            return response()->json([
                'status' => true,
                'message' => 'Absen pulang berhasil!',
                'deepface' => $deepface
            ]);
        }
    }
