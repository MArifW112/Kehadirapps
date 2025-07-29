<?php

namespace App\Http\Controllers;

use App\Models\PengajuanIzin;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Notifikasi;

class PengajuanIzinController extends Controller
{
    public function index()
    {
        $pengajuan_izins = PengajuanIzin::with('karyawan')->latest()->get();
        return view('admin.pengajuan_izin.index', compact('pengajuan_izins'));
    }

    public function show($id)
    {
        $izin = PengajuanIzin::with('karyawan')->findOrFail($id);
        return view('admin.pengajuan_izin.show', compact('izin'));
    }

    public function destroy($id)
    {
        $izin = PengajuanIzin::findOrFail($id);

        if ($izin->foto_bukti && Storage::disk('public')->exists($izin->foto_bukti)) {
            Storage::disk('public')->delete($izin->foto_bukti);
        }

        $izin->delete();

        return redirect()->route('admin.pengajuan_izin.index')->with('success', 'Data izin berhasil dihapus.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Menunggu,Disetujui,Ditolak',
        ]);

        $izin = PengajuanIzin::findOrFail($id);
        $izin->status = $request->status;
        $izin->save();

        Notifikasi::create([
            'karyawan_id' => $izin->karyawan_id,
            'judul' => 'Status Pengajuan Izin',
            'pesan' => 'Pengajuan izin kamu telah ' . strtolower($request->status) . '.',
            'status' => 'unread'
        ]);

        return back()->with('success', 'Status izin berhasil diperbarui.');
    }
}
