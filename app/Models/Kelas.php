<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    use HasFactory;

    protected $table = 'kelas';

    protected $fillable = ['nama_kelas', 'id_jurusan'];

    // Relasi ke Siswa
    public function siswa()
    {
        return $this->hasMany(Siswa::class, 'id_kelas');
    }

    public function jurusan(){
        return $this->belongsTo(Jurusan::class, 'id_jurusan');
    }

}
