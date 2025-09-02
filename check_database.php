<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Checking current table structure...\n";

try {
    // Lihat struktur tabel saat ini
    $columns = DB::select('DESCRIBE absensi_siswa');
    echo "Current table structure:\n";
    foreach ($columns as $column) {
        echo "- {$column->Field} ({$column->Type}) - {$column->Null} - {$column->Default}\n";
    }

    echo "\nChecking data...\n";

    // Lihat data yang ada
    $count = DB::table('absensi_siswa')->count();
    echo "Total records: {$count}\n";

    if ($count > 0) {
        $samples = DB::table('absensi_siswa')->limit(5)->get();
        echo "Sample data:\n";
        foreach ($samples as $sample) {
            $waktu = isset($sample->waktu) ? $sample->waktu : 'NULL';
            echo "ID: {$sample->id}, Siswa: {$sample->id_siswa}, Waktu: {$waktu}, Status: {$sample->status}\n";
        }

        // Cek apakah ada data dengan tanggal NULL atau invalid
        $invalidDates = DB::table('absensi_siswa')
            ->whereNull('tanggal')
            ->orWhere('tanggal', '0000-00-00')
            ->orWhere('tanggal', '')
            ->count();

        echo "Records with invalid dates: {$invalidDates}\n";

        if ($invalidDates > 0) {
            echo "Fixing invalid dates...\n";
            DB::table('absensi_siswa')
                ->whereNull('tanggal')
                ->orWhere('tanggal', '0000-00-00')
                ->orWhere('tanggal', '')
                ->update(['tanggal' => now()->format('Y-m-d')]);
            echo "Fixed {$invalidDates} records\n";
        }
    }

    echo "Database check completed.\n";

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
