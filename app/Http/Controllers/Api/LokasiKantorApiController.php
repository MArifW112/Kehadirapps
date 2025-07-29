<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LokasiKantor;

class LokasiKantorApiController extends Controller
{
    public function index()
    {
        $lokasi = LokasiKantor::first();
        if (!$lokasi) {
            return response()->json([
                'success' => false,
                'message' => 'Lokasi kantor belum diset.',
                'data' => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'nama_kantor'   => $lokasi->nama_kantor,
                'latitude'      => $lokasi->latitude,
                'longitude'     => $lokasi->longitude,
                'radius_meter'  => $lokasi->radius_meter,
            ]
        ]);
    }
}
