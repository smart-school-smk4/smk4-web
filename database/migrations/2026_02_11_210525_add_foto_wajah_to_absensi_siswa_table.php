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
        Schema::table('absensi_siswa', function (Blueprint $table) {
            // Menambahkan kolom foto_wajah untuk menyimpan path file foto
            $table->string('foto_wajah', 255)->nullable()->after('keterangan');
            
            // Menambahkan kolom foto_wajah_keluar untuk foto saat pulang
            $table->string('foto_wajah_keluar', 255)->nullable()->after('foto_wajah');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('absensi_siswa', function (Blueprint $table) {
            $table->dropColumn(['foto_wajah', 'foto_wajah_keluar']);
        });
    }
};
