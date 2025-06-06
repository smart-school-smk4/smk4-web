<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AbsensiGuru extends Model
{
    use HasFactory;

    protected $table = 'absensi_guru';

    protected $fillable = [
        'id_guru',
        'id_devices',
        'waktu',
        'status',
    ];

    //Relasi ke tabel devices
    public function devices()
    {
        return $this->belongsTo(Device::class, 'id_devices');
    }
    
    // Relasi ke tabel siswa
    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'id_guru');
    }
}
