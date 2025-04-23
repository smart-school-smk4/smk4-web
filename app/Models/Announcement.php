<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'content',
        'target_ruangans', // Menggunakan 'ruangan' bukan 'target_ruangans'
        'duration',
        'sent_at',
        'is_active',
        'status',
        'audio_url'
    ];

    protected $casts = [
        'target_ruangans' => 'array',
        'sent_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Relasi ke model Ruangan
     */
    public function ruangans()
    {
        return Ruangan::whereIn('nama', $this->ruangan)->get();
    }
}