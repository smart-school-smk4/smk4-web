<?php

namespace Database\Seeders;

use App\Models\Kelas;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KelasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Kelas::insert([
            [
                'nama_kelas' => 'X DKV 1',
                'id_jurusan' => 1, // Referencing Jurusan ID
            ],
            [
                'nama_kelas' => 'X DKV 2',
                'id_jurusan' => 1, // Referencing Jurusan ID
            ],
        ]);
    }
}
