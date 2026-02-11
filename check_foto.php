<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\AbsensiSiswa;

echo "=== Cek Data Foto Absensi ===\n\n";

$recent = AbsensiSiswa::with('siswa')
    ->latest('tanggal')
    ->latest('waktu_masuk')
    ->take(5)
    ->get();

if ($recent->count() > 0) {
    foreach ($recent as $i => $absensi) {
        echo "#{$i}.ID: {$absensi->id}\n";
        echo "  Siswa: " . ($absensi->siswa->nama_siswa ?? 'N/A') . "\n";
        echo "  Tanggal: {$absensi->tanggal}\n";
        echo "  Foto Masuk: " . ($absensi->foto_wajah ?? 'NULL') . "\n";
        echo "  Foto Keluar: " . ($absensi->foto_wajah_keluar ?? 'NULL') . "\n";
        echo "\n";
    }
} else {
    echo "Tidak ada data absensi\n";
}

echo "=== Total absensi: " . AbsensiSiswa::count() . " ===\n";
