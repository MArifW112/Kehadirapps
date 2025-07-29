<?php

namespace App\Notifications;

use App\Models\PengajuanIzin;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PengajuanIzinBaru extends Notification
{
    use Queueable;

    protected $izin;

    public function __construct(PengajuanIzin $izin)
    {
        $this->izin = $izin;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => 'Pengajuan izin baru oleh: ' . $this->izin->karyawan->nama_karyawan .
                ' ('. $this->izin->jenis .') pada tanggal ' . date('d-m-Y', strtotime($this->izin->tanggal)),
            'izin_id' => $this->izin->id,
            'karyawan_id' => $this->izin->karyawan_id,
        ];
    }
}
