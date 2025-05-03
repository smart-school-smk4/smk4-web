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
            
            // Hari dalam format Indonesia
            $table->enum('hari', [
                'Senin', 
                'Selasa', 
                'Rabu', 
                'Kamis', 
                'Jumat', 
                'Sabtu', 
                'Minggu'
            ]);
            
            // Waktu dalam format HH:MM:SS
            $table->time('waktu');
            
            // Nomor file 4 digit (0001-9999)
            $table->char('file_number', 4);
            
            // Tipe trigger
            $table->enum('trigger_type', ['schedule', 'manual'])
                  ->default('schedule');
                  
            // Volume 0-30
            $table->unsignedTinyInteger('volume')
                  ->default(15);
                  
            // Repeat 1-5 kali
            $table->unsignedTinyInteger('repeat')
                  ->default(1);
                  
            // Waktu bel berbunyi
            $table->timestamp('ring_time')
                  ->useCurrent();
                  
            $table->timestamps();

            // Optimasi query
            $table->index('ring_time');
            $table->index(['hari', 'waktu']);
            $table->index('file_number');
            
            // Constraint untuk memastikan data unik
            $table->unique([
                'hari', 
                'waktu', 
                'file_number', 
                'ring_time'
            ], 'bell_event_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('bell_histories');
    }
};