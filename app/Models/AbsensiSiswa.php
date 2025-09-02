<?php
namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AbsensiSiswa extends Model
{
    use HasFactory;

    protected $table = 'absensi_siswa';

    protected $fillable = [
        'id_siswa',
        'id_devices',
        'tanggal',
        'waktu_masuk',
        'waktu_keluar',
        'status',
        'keterangan',
    ];

    protected $casts = [
        'tanggal'      => 'date',
        'waktu_masuk'  => 'datetime',
        'waktu_keluar' => 'datetime',
    ];

    // Relasi ke tabel devices
    public function devices()
    {
        return $this->belongsTo(Devices::class, 'id_devices');
    }

    // Relasi ke tabel siswa
    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'id_siswa');
    }

    // Scope untuk filter berdasarkan tanggal
    public function scopeByDate($query, $date)
    {
        return $query->whereDate('tanggal', $date);
    }

    // Scope untuk filter berdasarkan bulan dan tahun
    public function scopeByMonth($query, $month, $year)
    {
        return $query->whereMonth('tanggal', $month)->whereYear('tanggal', $year);
    }

    // Method untuk cek apakah sudah absen masuk
    public function hasMasuk()
    {
        return ! is_null($this->waktu_masuk);
    }

    // Method untuk cek apakah sudah absen keluar
    public function hasKeluar()
    {
        return ! is_null($this->waktu_keluar);
    }

    // Method untuk menentukan status berdasarkan waktu masuk
    public function determineStatus($settingPresensi = null)
    {
        if (! $this->waktu_masuk) {
            return 'alpha';
        }

        if ($settingPresensi) {
            $jamMasuk       = Carbon::parse($settingPresensi->jam_masuk);
            $batasTerlambat = $jamMasuk->addMinutes($settingPresensi->toleransi_terlambat ?? 15);

            if (Carbon::parse($this->waktu_masuk)->gt($batasTerlambat)) {
                return 'terlambat';
            }
        }

        return 'hadir';
    }
}
