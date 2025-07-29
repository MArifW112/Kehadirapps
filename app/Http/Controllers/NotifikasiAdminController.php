<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotifikasiAdminController extends Controller
{
    public function index()
    {
        $notifikasi = auth()->user()->notifications()->latest()->take(30)->get();
        return view('admin.notifikasi', compact('notifikasi'));
    }

    // Untuk popup (AJAX)
    public function popup()
    {
        $notifikasi = auth()->user()->unreadNotifications()->latest()->take(5)->get();
        // Kirim partial view atau json, di sini pakai JSON
        $html = view('admin._notif_popup', compact('notifikasi'))->render();
        $jumlah = $notifikasi->count();
        return response()->json([
            'html' => $html,
            'jumlah' => $jumlah
        ]);
    }

    // Untuk menandai sudah dibaca (AJAX)
    public function markAsRead(Request $request)
    {
        $ids = $request->input('ids', []);
        auth()->user()->notifications()->whereIn('id', $ids)->update(['read_at' => now()]);
        return response()->json(['success' => true]);
    }
}
