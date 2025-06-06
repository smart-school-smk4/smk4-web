<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Devices extends Model
{
    protected $fillable = ['nama_device', 'id_kelas', 'id_ruangan'];
    
    public function kelas(){
    return $this->belongsTo(Kelas::class);
    }
    
    public function ruangan(){
        return $this->belongsTo(Ruangan::class);
    }
}
