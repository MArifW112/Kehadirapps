<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LokasiKantor;

class LokasiKantorController extends Controller
{
    public function index()
    {
        $lokasi = LokasiKantor::first();
        return view('admin.lokasi_kantor.index', compact('lokasi'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'nama_kantor' => 'required',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius_meter' => 'required|integer|min:50|max:1000',
        ]);

        $lokasi = LokasiKantor::first();
        if (!$lokasi) $lokasi = new LokasiKantor();

        $lokasi->nama_kantor = $request->nama_kantor;
        $lokasi->latitude = $request->latitude;
        $lokasi->longitude = $request->longitude;
        $lokasi->radius_meter = $request->radius_meter;
        $lokasi->save();

        return redirect()->back()->with('success', 'Lokasi kantor berhasil diupdate!');
    }
}
