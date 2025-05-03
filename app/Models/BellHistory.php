<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class BellHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'hari',
        'waktu',
        'file_number', 
        'trigger_type',
        'volume',
        'repeat',
        'ring_time'
    ];
    
    protected $casts = [
        'ring_time' => 'datetime',
        'waktu' => 'datetime:H:i:s', // Format waktu konsisten
    ];
    

    // Validasi otomatis saat create/update
    public static function rules()
    {
        return [
            'hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'waktu' => 'required|date_format:H:i:s',
            'file_number' => 'required|string|size:4|regex:/^[0-9]{4}$/',
            'trigger_type' => 'required|in:schedule,manual',
            'volume' => 'sometimes|integer|min:0|max:30',
            'repeat' => 'sometimes|integer|min:1|max:5'
        ];
    }

    // Normalisasi waktu sebelum simpan
    public function setWaktuAttribute($value)
    {
        try {
            $this->attributes['waktu'] = Carbon::createFromFormat('H:i:s', $value)->format('H:i:s');
        } catch (\Exception $e) {
            // Fallback ke format default jika parsing gagal
            $this->attributes['waktu'] = $value;
        }
    }

    
}