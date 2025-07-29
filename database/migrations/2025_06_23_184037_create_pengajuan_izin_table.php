<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
    Schema::create('pengajuan_izin', function (Blueprint $table) {
        $table->id();
        $table->foreignId('karyawan_id')->constrained('karyawans')->onDelete('cascade');
        $table->string('jenis'); // contoh: sakit, cuti, dll
        $table->text('alasan');
        $table->date('tanggal');
        $table->string('foto_bukti')->nullable();
        $table->string('status')->default('menunggu'); // menunggu, disetujui, ditolak
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengajuan_izin');
    }
};
