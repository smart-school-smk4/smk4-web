<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('jadwal_bels', function (Blueprint $table) {
            // Drop the old constraint
            $table->dropUnique(['hari', 'file_number']);
            
            // Add new constraint with time
            $table->unique(['hari', 'waktu', 'file_number']);
        });
    }

    public function down()
    {
        Schema::table('jadwal_bels', function (Blueprint $table) {
            $table->dropUnique(['hari', 'waktu', 'file_number']);
            $table->unique(['hari', 'file_number']);
        });
    }
};
