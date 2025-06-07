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
        'id_devices',
        'waktu',
        'status',
    ];

    //Relasi ke tabel devices
    public function devices()
    {
        return $this->belongsTo(Devices::class, 'id_devices');
    }
    
    // Relasi ke tabel siswa
    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'id_siswa');
    }
}
