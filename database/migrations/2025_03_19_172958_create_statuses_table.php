<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStatusesTable extends Migration
{
    public function up()
    {
        Schema::create('statuses', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->boolean('rtc')->default(false); // Status RTC
            $table->boolean('dfplayer')->default(false); // Status DFPlayer
            $table->string('rtc_time')->nullable(); // Waktu RTC
            $table->timestamp('last_communication')->nullable(); // Terakhir komunikasi
            $table->timestamp('last_sync')->nullable(); // Terakhir sinkronisasi
            $table->string('status')->nullable(); // Status umum perangkat
            $table->timestamps(); // Kolom created_at dan updated_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('statuses');
    }
}