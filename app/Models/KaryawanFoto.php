<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KaryawanFoto extends Model
{
    protected $fillable = ['karyawan_id', 'path'];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }
    public function notifikasis()
{
    return $this->hasMany(Notifikasi::class);
}

}
