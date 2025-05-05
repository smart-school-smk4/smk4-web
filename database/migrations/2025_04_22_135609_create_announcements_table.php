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
            $table->text('message')->nullable();
            $table->string('mode')->default('tts');
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });

        Schema::create('announcement_ruangan', function (Blueprint $table) {
            $table->foreignId('announcement_id')->constrained('announcements')->onDelete('cascade');
            $table->foreignId('ruangan_id')->constrained('ruangan')->onDelete('cascade');
            $table->primary(['announcement_id', 'ruangan_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('announcement_ruangan');
        Schema::dropIfExists('announcements');
    }
};