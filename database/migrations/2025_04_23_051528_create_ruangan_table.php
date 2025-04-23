<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ruangan', function (Blueprint $table) {
            $table->id();
            $table->string('nama_ruangan');
            
            // Foreign keys
            $table->foreignId('kelas_id')->constrained('kelas')->onDelete('cascade');
            $table->foreignId('jurusan_id')->constrained('jurusan')->onDelete('cascade');
            
            $table->timestamps();
            
            // Tambahkan index untuk pencarian
            $table->index('nama_ruangan');
            $table->index(['kelas_id', 'jurusan_id']);
        });
    }

    public function down(): void
    {
        Schema::table('ruangan', function (Blueprint $table) {
            $table->dropForeign(['kelas_id']);
            $table->dropForeign(['jurusan_id']);
        });
        
        Schema::dropIfExists('ruangan');
    }
};