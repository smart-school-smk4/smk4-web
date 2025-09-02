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
        Schema::create('absensi_siswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_siswa')->constrained('siswa')->onDelete('cascade');
            $table->foreignId('id_devices')->nullable()->constrained('devices')->onDelete('set null');
            $table->date('tanggal');                       // Tanggal absensi
            $table->timestamp('waktu_masuk')->nullable();  // Waktu masuk
            $table->timestamp('waktu_keluar')->nullable(); // Waktu keluar
            $table->enum('status', ['hadir', 'terlambat', 'sakit', 'izin', 'alpha'])->default('alpha');
            $table->text('keterangan')->nullable();
            $table->timestamps();

            // Index untuk performa query
            $table->index(['id_siswa', 'tanggal']);
            $table->index('tanggal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absensi_siswa');
    }
};
