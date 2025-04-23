<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnnouncementRoomTable extends Migration
{
    public function up()
    {
        Schema::create('announcement_room', function (Blueprint $table) {
            $table->unsignedBigInteger('announcement_id'); // ID pengumuman
            $table->string('room_name'); // Nama ruangan

            // Primary key gabungan
            $table->primary(['announcement_id', 'room_name']);

            // Foreign keys
            $table->foreign('announcement_id')->references('id')->on('announcements')->onDelete('cascade');
            $table->foreign('room_name')->references('name')->on('rooms')->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('announcement_room');
    }
}