<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FaceRecognitionController extends Controller
{
    public function predict(Request $request)
{
    // Validasi file
    $request->validate([
        'foto' => 'required|image|max:2048'
    ]);

    // Simpan file sementara
    $image = $request->file('foto');
    $imagePath = $image->store('tmp_face', 'public');
    $absPath = storage_path('app/public/' . $imagePath);

    // Jalankan python face_recog_predict.py
    $cmd = 'python "D:/Aplikasi Mobile Kehadirapps/Admin/ai_face_recog/predict.py" "' . $absPath . '" 2>&1';
    \Log::info("Path file dikirim ke python:", [$absPath]);
    $output = shell_exec($cmd);
    \Log::info("Output Python:", [$output]);


    // Parse output python (misal "Prediksi label: 1, confidence: 0.999")
    preg_match('/label: (\d+), confidence: ([\d\.]+)/', $output, $matches);

    if ($matches) {
        $karyawan = \App\Models\Karyawan::find($matches[1]);
        return response()->json([
            'success' => true,
            'label' => $matches[1],
            'confidence' => $matches[2],
            'nama' => $karyawan ? $karyawan->nama_karyawan : 'Tidak ditemukan'
        ]);
    } else {
        return response()->json(['success' => false, 'message' => 'Wajah tidak dikenali!']);
    }
}
}
