<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $fillable = ['nama_device', 'kelas_id'];
    
    public function kelas(){
        return $this->belongsTo(Kelas::class);
    }

    public function absensi(){
        return $this->hasMany(AbsensiSiswa::class, 'device_id');
    }
}
