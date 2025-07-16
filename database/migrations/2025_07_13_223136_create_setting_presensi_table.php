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
        Schema::create('setting_presensi', function (Blueprint $table) {
            $table->id();
            $table->time('waktu_masuk_mulai');
            $table->time('waktu_masuk_selesai');
            $table->time('waktu_pulang_mulai');
            $table->time('waktu_pulang_selesai');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('setting_presensi');
    }
};
