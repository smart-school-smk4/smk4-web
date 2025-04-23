<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class JadwalBel extends Model
{
    protected $fillable = [
        'hari',
        'waktu',
        'file_number',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Ini dia! Tambahin di sini
    protected $appends = ['formatted_time'];

    const DAYS = [
        'Senin', 'Selasa', 'Rabu',
        'Kamis', 'Jumat', 'Sabtu', 'Minggu',
    ];

    /**
     * Scope untuk jadwal aktif
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk urutan hari
     */
    public function scopeOrderByDay(Builder $query): Builder
    {
        return $query->orderByRaw(
            "FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu')"
        );
    }

    /**
     * Akses waktu dalam format H:i:s
     */
    public function getFormattedTimeAttribute(): string
    {
        return date('H:i:s', strtotime($this->waktu));
    }
}
