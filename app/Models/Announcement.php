<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;

    protected $table = 'announcements';

    protected $fillable = [
        'mode',
        'message',
        'audio_path',
        'voice',
        'speed',
        'ruangan',
        'user_id',
        'sent_at'
    ];

    protected $casts = [
        'ruangan' => 'array',
        'sent_at' => 'datetime'
    ];

    /**
     * Relasi ke user yang membuat pengumuman
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi many-to-many ke ruangan
     */
    public function ruangans()
    {
        return $this->belongsToMany(Ruangan::class, 'announcement_ruangan');
    }

    /**
     * Scope untuk pengumuman reguler
     */
    public function scopeReguler($query)
    {
        return $query->where('mode', 'reguler');
    }

    /**
     * Scope untuk pengumuman TTS
     */
    public function scopeTts($query)
    {
        return $query->where('mode', 'tts');
    }

    /**
     * Scope untuk pencarian
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('message', 'like', "%{$search}%")
                    ->orWhereHas('ruangans', function($q) use ($search) {
                        $q->where('nama_ruangan', 'like', "%{$search}%");
                    })
                    ->orWhereHas('user', function($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
    }

    /**
     * Accessor untuk audio URL
     */
    public function getAudioUrlAttribute()
    {
        return $this->audio_path ? asset('storage/' . $this->audio_path) : null;
    }

    /**
     * Format tanggal pengiriman
     */
    public function getFormattedSentAtAttribute()
    {
        return $this->sent_at->format('d M Y H:i:s');
    }
}