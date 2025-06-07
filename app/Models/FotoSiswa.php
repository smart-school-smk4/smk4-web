<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class FotoSiswa extends Model
{
    use HasFactory;

    protected $table = 'foto_siswa';
    protected $fillable = ['id_siswa', 'path'];

    // Relasi balik ke Siswa
    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'id_siswa');
    }

    // Accessor untuk mendapatkan URL lengkap ke foto
    public function getUrlAttribute()
    {
        return Storage::url($this->path);
    }
}
