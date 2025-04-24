<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('bell_histories', function (Blueprint $table) {
            $table->id();
            $table->string('hari');
            $table->time('waktu');
            $table->string('file_number', 4);
            $table->enum('trigger_type', ['schedule', 'manual']);
            $table->integer('volume');
            $table->integer('repeat');
            $table->timestamp('ring_time');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bell_histories');
    }
};