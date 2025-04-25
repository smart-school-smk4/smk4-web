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
            $table->string('mode'); // 'reguler' atau 'tts'
            $table->text('message');
            $table->string('audio_path')->nullable();
            $table->string('voice')->nullable();
            $table->integer('speed')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamp('sent_at')->useCurrent();
            $table->timestamps();
            
            $table->index('mode');
            $table->index('sent_at');
            $table->index('user_id');
        });

        // Perbaikan utama: Explicitly specify table names
        Schema::create('announcement_ruangan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('announcement_id')->constrained('announcements')->onDelete('cascade');
            $table->foreignId('ruangan_id')->constrained('ruangan')->onDelete('cascade');
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