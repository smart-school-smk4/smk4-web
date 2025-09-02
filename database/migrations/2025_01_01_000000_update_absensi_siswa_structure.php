<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Cek dan tambahkan kolom yang hilang pada tabel absensi_siswa
        Schema::table('absensi_siswa', function (Blueprint $table) {
            // Tambah kolom tanggal jika belum ada
            if (! Schema::hasColumn('absensi_siswa', 'tanggal')) {
                $table->date('tanggal')->default(DB::raw('CURDATE()'))->after('id_devices');
            }

            // Tambah kolom waktu_masuk jika belum ada
            if (! Schema::hasColumn('absensi_siswa', 'waktu_masuk')) {
                $table->timestamp('waktu_masuk')->nullable()->after('tanggal');
            }

            // Tambah kolom waktu_keluar jika belum ada
            if (! Schema::hasColumn('absensi_siswa', 'waktu_keluar')) {
                $table->timestamp('waktu_keluar')->nullable()->after('waktu_masuk');
            }

            // Tambah kolom keterangan jika belum ada
            if (! Schema::hasColumn('absensi_siswa', 'keterangan')) {
                $table->text('keterangan')->nullable();
            }
        });

        // Update status enum jika perlu
        $currentStatusColumn = DB::select("SHOW COLUMNS FROM absensi_siswa WHERE Field = 'status'")[0];

        if (! str_contains($currentStatusColumn->Type, 'terlambat')) {
            DB::statement("ALTER TABLE absensi_siswa MODIFY COLUMN status ENUM('hadir', 'terlambat', 'sakit', 'izin', 'alpha') DEFAULT 'alpha'");
        }

        // Set tanggal default untuk record yang belum ada tanggal
        DB::statement("UPDATE absensi_siswa SET tanggal = CURDATE() WHERE tanggal IS NULL OR tanggal = '0000-00-00'");

        // Tambah index untuk performa jika belum ada
        $indexes = collect(DB::select("SHOW INDEX FROM absensi_siswa"))->pluck('Key_name');

        if (! $indexes->contains('idx_siswa_tanggal')) {
            DB::statement("ALTER TABLE absensi_siswa ADD INDEX idx_siswa_tanggal (id_siswa, tanggal)");
        }

        if (! $indexes->contains('idx_tanggal')) {
            DB::statement("ALTER TABLE absensi_siswa ADD INDEX idx_tanggal (tanggal)");
        }

        if (! $indexes->contains('idx_status')) {
            DB::statement("ALTER TABLE absensi_siswa ADD INDEX idx_status (status)");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Hapus index
        try {
            DB::statement("ALTER TABLE absensi_siswa DROP INDEX idx_siswa_tanggal");
        } catch (\Exception $e) {}

        try {
            DB::statement("ALTER TABLE absensi_siswa DROP INDEX idx_tanggal");
        } catch (\Exception $e) {}

        try {
            DB::statement("ALTER TABLE absensi_siswa DROP INDEX idx_status");
        } catch (\Exception $e) {}

        // Kembalikan status enum ke format lama jika diperlukan
        DB::statement("ALTER TABLE absensi_siswa MODIFY COLUMN status ENUM('masuk', 'pulang') DEFAULT 'masuk'");

        // Hapus kolom tambahan (opsional, untuk kembalikan ke struktur asli)
        Schema::table('absensi_siswa', function (Blueprint $table) {
            if (Schema::hasColumn('absensi_siswa', 'keterangan')) {
                $table->dropColumn('keterangan');
            }
        });
    }
};
