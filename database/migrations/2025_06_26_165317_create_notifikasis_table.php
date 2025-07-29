<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifikasis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('karyawan_id')->constrained()->onDelete('cascade'); // Relasi ke karyawan
            $table->string('judul');           // Judul singkat, misal "Status Izin"
            $table->text('pesan');             // Pesan detail, misal "Izin Anda telah disetujui"
            $table->boolean('status_baca')->default(false); // Sudah/belum dibaca
            $table->timestamps();              // created_at, updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifikasis');
    }
};
