<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Devices extends Model
{
    protected $table = 'devices';

    /**
     * Kolom yang bisa diisi secara massal (mass assignable).
     * Pastikan semua kolom dari form ada di sini.
     */
    protected $fillable = [
        'nama_device',
        'ip_address',
        'id_kelas',
        'id_ruangan',
    ];

    /**
     * Mendefinisikan relasi "belongsTo" ke model Kelas.
     * Ini memberitahu Laravel bahwa satu device "milik" satu kelas.
     */
    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'id_kelas');
    }

    /**
     * Mendefinisikan relasi "belongsTo" ke model Ruangan.
     * Ini memberitahu Laravel bahwa satu device "milik" satu ruangan.
     */
    public function ruangan()
    {
        return $this->belongsTo(Ruangan::class, 'id_ruangan');
    }
}
