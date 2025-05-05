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
        'id_jurusan',
        'relay_state'
    ];

    protected $casts = [
        'relay_state' => 'string'
    ];

    /**
     * Relationship with Kelas
     */
    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'id_kelas');
    }

    /**
     * Relationship with Jurusan
     */
    public function jurusan()
    {
        return $this->belongsTo(Jurusan::class, 'id_jurusan');
    }

    /**
     * Relationship with Announcements (many-to-many)
     */
    public function announcements()
    {
        return $this->belongsToMany(Announcement::class, 'announcement_ruangan');
    }

    /**
     * Accessor for uppercase room name
     */
    public function getNamaRuanganAttribute($value)
    {
        return strtoupper($value);
    }

}