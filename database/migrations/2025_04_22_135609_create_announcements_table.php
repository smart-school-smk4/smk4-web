<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('mode');
            $table->text('message');
            $table->string('audio_path')->nullable();
            $table->string('voice')->nullable();
            $table->integer('speed')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('status')->default('pending');
            $table->text('error_message')->nullable();
            $table->string('relay_state')->default('OFF'); // Tambahkan kolom ini
            $table->timestamp('sent_at')->useCurrent();
            $table->timestamps();
            
            $table->index('mode');
            $table->index('sent_at');
            $table->index('relay_state'); // Tambahkan index
        });

        // Perbaikan utama: Explicitly specify table names
        Schema::create('announcement_ruangan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('announcement_id')->constrained('announcements')->onDelete('cascade');
            $table->foreignId('ruangan_id')->constrained('ruangan')->onDelete('cascade');
            $table->string('relay_state_at_time')->nullable(); // State saat pengumuman dikirim
            $table->timestamps();
            
            $table->unique(['announcement_id', 'ruangan_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('announcement_ruangan');
        Schema::dropIfExists('announcements');
    }
};