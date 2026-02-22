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
        Schema::table('siswa', function (Blueprint $table) {
            $table->integer('nomer_absen')->nullable()->after('nama_siswa');
            // Add unique constraint for nomer_absen per kelas
            $table->unique(['nomer_absen', 'id_kelas'], 'siswa_nomer_absen_kelas_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('siswa', function (Blueprint $table) {
            $table->dropUnique('siswa_nomer_absen_kelas_unique');
            $table->dropColumn('nomer_absen');
        });
    }
};
