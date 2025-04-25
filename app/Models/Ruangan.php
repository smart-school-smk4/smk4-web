<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ruangan extends Model
{
    use HasFactory;

    protected $table = 'ruangan';

    protected $fillable = [
        'nama_ruangan',
        'id_kelas',
        'id_jurusan'
    ];

    /**
     * Relasi ke model Kelas
     */
    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'id_kelas');
    }

    /**
     * Relasi ke model Jurusan
     */
    public function jurusan()
    {
        return $this->belongsTo(Jurusan::class, 'id_jurusan');
    }


    /**
     * Accessor untuk nama ruangan
     */
    public function getNamaRuanganAttribute($value)
    {
        return strtoupper($value);
    }

    /**
     * Mutator untuk nama ruangan
     */
    public function setNamaRuanganAttribute($value)
    {
        $this->attributes['nama_ruangan'] = strtolower($value);
    }

    /**
     * Scope untuk pencarian ruangan
     */
    public function scopeSearch($query, $term)
    {
        return $query->where('nama_ruangan', 'like', "%{$term}%")
                    ->orWhereHas('kelas', function($q) use ($term) {
                        $q->where('nama_kelas', 'like', "%{$term}%");
                    })
                    ->orWhereHas('jurusan', function($q) use ($term) {
                        $q->where('nama_jurusan', 'like', "%{$term}%");
                    });
    }
}