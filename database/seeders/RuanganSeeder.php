<?php

namespace Database\Seeders;

use App\Models\Ruangan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RuanganSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Ruangan::insert([
            [
                'nama_ruangan' => 'Lab_DKV',
                'id_kelas' => 1, // Referencing Kelas ID
                'id_jurusan' => 1, // Referencing Jurusan ID
                'relay_state' => 'off',
            ],
        ]);
    }
}
