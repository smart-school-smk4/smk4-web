<?php

namespace Database\Seeders;

use App\Models\Guru;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GuruSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       Guru::insert([
           [
               'nama_guru' => 'Ridwan',
               'jabatan' => 'Guru',
               'no_hp_guru' => '081234567',
               'email_guru' => 'ridwan@gmail'
           ],
           [
               'nama_guru' => 'Jamal',
               'jabatan' => 'Guru',
               'no_hp_guru' => '0891238217',
               'email_guru' => 'jamal@gmail'
           ],
           [
               'nama_guru' => 'Budi Speed',
               'jabatan' => 'Guru',
               'no_hp_guru' => '08192312',
               'email_guru' => 'Budi Speed@gmail'
           ]
       ]);
    }
}
