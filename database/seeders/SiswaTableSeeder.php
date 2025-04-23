<?php

namespace Database\Seeders;

use App\Models\Siswa;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SiswaTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Siswa::insert([
            [
                'nama_siswa' => 'Aldo Wijaya',
                'nisn' => '1234567890',
                'tanggal_lahir' => '2006-05-12',
                'foto_siswa' => 'aldo.jpg',
                'jenis_kelamin' => 'L',
                'alamat' => 'Jl. Merdeka No. 1',
                'no_hp' => '081234567890',
                'email' => 'aldo@example.com',
                'id_jurusan' => 1,
                'id_kelas' => 1,
            ],
            [
                'nama_siswa' => 'Salsa Mutiara',
                'nisn' => '1234567891',
                'tanggal_lahir' => '2006-03-25',
                'foto_siswa' => 'salsa.jpg',
                'jenis_kelamin' => 'P',
                'alamat' => 'Jl. Mawar No. 7',
                'no_hp' => '081234567891',
                'email' => 'salsa@example.com',
                'id_jurusan' => 1,
                'id_kelas' => 2,
            ],
            [
                'nama_siswa' => 'Raihan Pratama',
                'nisn' => '1234567892',
                'tanggal_lahir' => '2006-01-10',
                'foto_siswa' => 'raihan.jpg',
                'jenis_kelamin' => 'L',
                'alamat' => 'Jl. Kenanga No. 3',
                'no_hp' => '081234567892',
                'email' => 'raihan@example.com',
                'id_jurusan' => 2,
                'id_kelas' => 3,
            ],
        ]);
    }
}
