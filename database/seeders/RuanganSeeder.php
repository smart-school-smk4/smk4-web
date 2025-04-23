<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Ruangan;
use App\Models\Kelas;
use App\Models\Jurusan;

class RuanganSeeder extends Seeder
{
    public function run(): void
    {
        // Hapus semua data ruangan sebelum menyisipkan data baru
        Ruangan::truncate();

        // Ambil semua ID kelas dan jurusan
        $kelasIds = Kelas::pluck('id')->toArray();
        $jurusanIds = Jurusan::pluck('id')->toArray();

        $data = [];
        foreach ($kelasIds as $kelasId) {
            foreach ($jurusanIds as $jurusanId) {
                // Ekstrak angka dari nama kelas
                $kelasNumber = (int) preg_replace('/[^0-9]/', '', Kelas::find($kelasId)->nama_kelas);
                $jurusanNumber = array_search($jurusanId, $jurusanIds) + 1;
                $nama_ruangan = "{$kelasNumber}.{$jurusanNumber}";

                // Pastikan kombinasi kelas_id dan jurusan_id unik
                if (!Ruangan::where('kelas_id', $kelasId)
                            ->where('jurusan_id', $jurusanId)
                            ->exists()) {
                    $data[] = [
                        'nama_ruangan' => $nama_ruangan,
                        'kelas_id' => $kelasId,
                        'jurusan_id' => $jurusanId,
                    ];
                }
            }
        }

        // Masukkan data ke tabel ruangan
        foreach ($data as $item) {
            Ruangan::create($item);
        }
    }
}