<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JadwalKerja extends Model
{
    protected $table = 'jadwal_kerja';
    protected $fillable = ['hari', 'jam_masuk', 'jam_pulang', 'aktif'];
}
