<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notifikasi;

class NotifikasiApiController extends Controller
{
    // Ambil semua notifikasi untuk karyawan (user)
    public function index(Request $request)
    {
        $karyawanId = $request->query('karyawan_id');
        $notif = Notifikasi::where('karyawan_id', $karyawanId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $notif
        ]);
    }

    // Tandai notifikasi sudah dibaca
    public function markAsRead(Request $request)
    {
        $karyawanId = $request->input('karyawan_id');
        if (!$karyawanId) {
            return response()->json(['status' => false, 'message' => 'karyawan_id wajib diisi'], 422);
        }

        // Update semua notifikasi milik karyawan ini yang belum dibaca
        \App\Models\Notifikasi::where('karyawan_id', $karyawanId)
            ->where('status_baca', false)
            ->update(['status_baca' => true]);

        return response()->json(['status' => true, 'message' => 'Semua notifikasi sudah dibaca']);
    }
    public function store(Request $request)
    {
        $request->validate([
            'karyawan_id' => 'required|exists:karyawans,id',
            'judul' => 'required',
            'pesan' => 'required',
        ]);

        $notif = Notifikasi::create([
            'karyawan_id' => $request->karyawan_id,
            'judul' => $request->judul,
            'pesan' => $request->pesan,
            'status_baca' => false,
        ]);

        return response()->json([
            'status' => true,
            'data' => $notif,
            'message' => 'Notifikasi berhasil dikirim!'
        ]);
    }
}
