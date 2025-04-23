<?php

namespace Database\Seeders; // Pastikan namespace ini benar

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StatusSeeder extends Seeder // Nama kelas harus sama dengan nama file
{
    public function run()
    {
        \App\Models\Status::updateOrCreate(
            ['id' => 1],
            [
                'rtc' => false,
                'dfplayer' => false,
                'last_communication' => null,
                'last_sync' => null
            ]
        );
    }
}