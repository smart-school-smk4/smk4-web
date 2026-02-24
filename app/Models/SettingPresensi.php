<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SettingPresensi extends Model
{
    protected $table = 'setting_presensi';

    protected $fillable = [
        'waktu_masuk_mulai',
        'waktu_masuk_selesai',
        'waktu_pulang_mulai',
        'waktu_pulang_selesai',
        'threshold_probabilitas',
    ];

    protected $casts = [
        'threshold_probabilitas' => 'decimal:2',
    ];

    public $timestamps = true;
}
