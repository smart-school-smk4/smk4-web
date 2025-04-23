<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AbsensiSiswa extends Model
{
    use HasFactory;

    protected $table = 'absensi_siswa';

    protected $fillable = [
        'id_siswa',
        'waktu_absen',
        'status',
    ];

    //Relasi ke tabel devices
    public function devices()
    {
        return $this->belongsTo(Device::class, 'devices_id');
    }
    
    // Relasi ke tabel siswa
    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'id_siswa');
    }
}
