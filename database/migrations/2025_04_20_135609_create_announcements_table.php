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
            $table->string('type'); // 'tts' or 'manual'
            $table->text('content')->nullable();
            $table->json('target_ruangans')->nullable();
            $table->timestamp('sent_at');
            $table->boolean('is_active')->default(false);
            $table->string('status')->default('pending');
            $table->string('audio_url')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('announcements');
    }
};