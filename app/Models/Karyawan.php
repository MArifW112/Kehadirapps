<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Karyawan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nama_karyawan',
        'email',
        'alamat',
        'no_hp',
        'jabatan',
        'foto',

    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
        public function fotos()
    {
        return $this->hasMany(KaryawanFoto::class);
    }
    public function absensis()
{
    return $this->hasMany(Absensi::class);
}
}
