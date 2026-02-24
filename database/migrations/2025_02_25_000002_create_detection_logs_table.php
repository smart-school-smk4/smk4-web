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
        Schema::create('detection_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained('devices')->onDelete('cascade');
            $table->string('student_id')->nullable(); // ID siswa dari face recognition
            $table->string('student_name'); // Nama siswa yang terdeteksi
            $table->string('nis')->nullable(); // NIS siswa
            $table->decimal('probability', 5, 4); // Akurasi deteksi (0.0000 - 1.0000)
            $table->timestamp('detected_at'); // Waktu deteksi
            $table->index(['device_id', 'detected_at']); // Index untuk query cepat
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detection_logs');
    }
};
