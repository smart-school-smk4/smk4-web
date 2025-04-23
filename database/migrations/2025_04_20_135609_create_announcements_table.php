<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnnouncementsTable extends Migration
{
    public function up()
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['tts', 'manual']); // Jenis pengumuman: TTS atau Manual
            $table->text('content')->nullable(); // Konten pengumuman (untuk TTS)
            $table->json('target_rooms'); // Daftar ruangan target (disimpan sebagai JSON)
            $table->integer('duration')->nullable(); // Durasi pengumuman (untuk manual)
            $table->timestamp('sent_at')->nullable(); // Waktu pengiriman
            $table->boolean('is_active')->default(false); // Status aktif/tidak
            $table->enum('status', ['processing', 'completed', 'stopped'])->default('processing'); // Status pengumuman
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('announcements');
    }
}