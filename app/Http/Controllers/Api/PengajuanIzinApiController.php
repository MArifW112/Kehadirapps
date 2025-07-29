<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PengajuanIzin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PengajuanIzinApiController extends Controller
{
    public function store(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'karyawan_id' => 'required|exists:karyawans,id',
            'jenis' => 'required|in:Sakit,Cuti,Keperluan Keluarga,Lainnya',
            'alasan' => 'required|string',
            'tanggal' => 'required|date',
            'foto_bukti' => 'nullable|image|max:10240', // max 10MB, optional
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        $pathFoto = null;
        if ($request->hasFile('foto_bukti')) {
            $pathFoto = $request->file('foto_bukti')->store('izin', 'public');
        }

        $izin = PengajuanIzin::create([
            'karyawan_id' => $request->karyawan_id,
            'jenis' => $request->jenis,
            'alasan' => $request->alasan,
            'tanggal' => $request->tanggal,
            'foto_bukti' => $pathFoto,
            'status' => 'Menunggu',
        ]);

        $admins = \App\Models\User::where('role', 'admin')->get();

        foreach($admins as $admin) {
            $admin->notify(new \App\Notifications\PengajuanIzinBaru($izin));
        }

        return response()->json([
            'status' => true,
            'message' => 'Pengajuan izin berhasil dikirim!',
            'data' => $izin,
        ]);
    }
}
