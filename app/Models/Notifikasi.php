<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notifikasi extends Model
{
    use HasFactory;

    protected $fillable = [
        'karyawan_id',
        'judul',
        'pesan',
        'status_baca',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }
}
