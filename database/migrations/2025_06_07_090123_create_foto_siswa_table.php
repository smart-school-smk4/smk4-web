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
        // Tabel baru untuk menyimpan path ke banyak foto untuk setiap siswa
        Schema::create('foto_siswa', function (Blueprint $table) {
            $table->id();
            // Menghubungkan ke tabel 'siswa'. Jika siswa dihapus, fotonya juga ikut terhapus.
            $table->foreignId('id_siswa')->constrained('siswa')->onDelete('cascade');
            // Kolom untuk menyimpan path file foto di storage
            $table->string('path');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('foto_siswa');
    }
};
