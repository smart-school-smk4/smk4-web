<?php

namespace Database\Seeders;

use App\Models\Devices;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DevicesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Devices::insert([
            [
                'nama_device' => 'R_Lab_TKK',
                'id_kelas' => 1, // Referencing Kelas ID
                'id_ruangan' => 1, // Referencing Ruangan ID
            ],
            [
                'nama_device' => 'R_Lab_TIF',
                'id_kelas' => 2, // Referencing Kelas ID
                'id_ruangan' => 2, // Referencing Ruangan ID
            ],
        ]);
    }
}
