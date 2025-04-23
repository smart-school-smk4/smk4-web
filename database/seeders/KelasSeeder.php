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
                'nama_kelas' => 'XII TKK 1',
                'id_jurusan' => 1, // Referencing Jurusan ID
            ],
            [
                'nama_kelas' => 'XII TKK 2',
                'id_jurusan' => 1, // Referencing Jurusan ID
            ],
            [
                'nama_kelas' => 'XII TIF 1',
                'id_jurusan' => 2, // Referencing Jurusan ID
            ],
            [
                'nama_kelas' => 'XII TIF 2',
                'id_jurusan' => 2, // Referencing Jurusan ID
            ],
        ]);
    }
}
