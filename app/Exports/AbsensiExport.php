<?php

namespace App\Exports;

use App\Models\Absensi;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AbsensiExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Absensi::with('karyawan')
            ->get()
            ->map(function ($absen) {
                return [
                    'Nama Karyawan' => $absen->karyawan->nama_karyawan ?? '-',
                    'Tanggal' => $absen->tanggal,
                    'Jam Masuk' => $absen->jam_masuk,
                    'Jam Pulang' => $absen->jam_pulang,
                    'Status' => $absen->status,
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Nama Karyawan',
            'Tanggal',
            'Jam Masuk',
            'Jam Pulang',
            'Status'
        ];
    }
}
