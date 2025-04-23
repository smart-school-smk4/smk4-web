<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\JadwalBel; // Tambahkan di bagian atas file

class CreateJadwalBelsTable extends Migration
{
    public function up()
    {
        Schema::create('jadwal_bels', function (Blueprint $table) {
            $table->id();
            $table->enum('hari', array_values(JadwalBel::DAYS)); // Refer ke konstanta model
            $table->time('waktu');
            $table->char('file_number', 4);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique(['hari', 'file_number']);
            
            // Tambahkan index untuk pencarian
            $table->index('hari');
            $table->index('waktu');
            $table->index('is_active');
        });
    }
    public function down()
    {
        Schema::dropIfExists('jadwal_bels');
    }
}