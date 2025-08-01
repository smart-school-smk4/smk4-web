<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    use HasFactory;

    protected $table = 'siswa'; // Nama tabel di database

    protected $fillable = [
        'nama_siswa',
        'nisn',
        'tanggal_lahir',
        'jenis_kelamin',
        'alamat',
        'no_hp',
        'email',
        'id_jurusan',
        'id_kelas',
    ];

    // Relasi ke Jurusan
    public function jurusan()
    {
        return $this->belongsTo(Jurusan::class, 'id_jurusan');
    }

    // Relasi ke Kelas
    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'id_kelas');
    }

    public function absensi()
    {
        return $this->hasMany(AbsensiSiswa::class, 'id_siswa');
    }

    public function fotos()
    {
        return $this->hasMany(FotoSiswa::class, 'id_siswa');
    }
}