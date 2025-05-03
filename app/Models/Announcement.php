<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;

    protected $table = 'announcements'; // Pastikan konsisten

    protected $fillable = [
        'mode',
        'message',
        'audio_path',
        'voice',
        'speed',
        'is_active',
        'status',
        'error_message',
        'sent_at',
        'relay_state' // Tambahkan ini
    ];

    protected $attributes = [
        'is_active' => true,
        'status' => 'pending',
        'relay_state' => 'OFF' // Default value
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sent_at' => 'datetime'
    ];

    // Tambahkan aksesor untuk relay
    public function getRelayStateDescriptionAttribute()
    {
        return $this->relay_state === 'ON' ? 'Relay Menyala' : 'Relay Mati';
    }

    /**
     * Relationship with Ruangan (many-to-many)
     */
    public function ruangans()
    {
        return $this->belongsToMany(Ruangan::class, 'announcement_ruangan')
                   ->withTimestamps();
    }

    /**
     * Scope for regular announcements
     */
    public function scopeReguler($query)
    {
        return $query->where('mode', 'reguler');
    }

    /**
     * Scope for TTS announcements
     */
    public function scopeTts($query)
    {
        return $query->where('mode', 'tts');
    }

    /**
     * Scope for delivered announcements
     */
    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    /**
     * Scope for failed announcements
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Accessor for audio URL
     */
    public function getAudioUrlAttribute()
    {
        return $this->audio_path ? asset('storage/' . $this->audio_path) : null;
    }

    /**
     * Accessor for formatted sent time
     */
    public function getFormattedSentAtAttribute()
    {
        return $this->sent_at->format('d M Y H:i:s');
    }

    /**
     * Accessor untuk pesan aktivasi
     */
    public function getActivationMessageAttribute()
    {
        return $this->is_active ? 'Aktivasi Ruangan' : 'Deaktivasi Ruangan';
    }

    /**
     * Cek apakah pengumuman reguler
     */
    public function isReguler()
    {
        return $this->mode === 'reguler';
    }

    /**
     * Cek apakah pengumuman TTS
     */
    public function isTts()
    {
        return $this->mode === 'tts';
    }
}