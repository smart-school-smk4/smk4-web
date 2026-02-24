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
        Schema::table('setting_presensi', function (Blueprint $table) {
            $table->decimal('threshold_probabilitas', 3, 2)->default(0.50)->after('waktu_pulang_selesai');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('setting_presensi', function (Blueprint $table) {
            $table->dropColumn('threshold_probabilitas');
        });
    }
};
