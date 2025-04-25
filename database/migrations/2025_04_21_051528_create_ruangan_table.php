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
            $table->foreignId('id_kelas')->constrained('kelas')->onDelete('cascade');
            $table->foreignId('id_jurusan')->constrained('jurusan')->onDelete('cascade');
            
            $table->timestamps();
            
            // Tambahkan index untuk pencarian
            $table->index('nama_ruangan');
            $table->index(['id_kelas', 'id_jurusan']);
        });
    }

    public function down(): void
    {
        Schema::table('ruangan', function (Blueprint $table) {
            $table->dropForeign(['id_kelas']);
            $table->dropForeign(['id_jurusan']);
        });
        
        Schema::dropIfExists('ruangan');
    }
};