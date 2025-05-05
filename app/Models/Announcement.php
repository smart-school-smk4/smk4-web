<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = ['message', 'mode', 'sent_at'];

    protected $casts = [
        'sent_at' => 'datetime',
    ];
    

    public function ruangans()
    {
        return $this->belongsToMany(Ruangan::class);
    }

    public function getFormattedSentAtAttribute()
    {
        return $this->sent_at 
            ? $this->sent_at->format('d/m/Y H:i:s')
            : 'Belum dikirim';
    }

    // Accessor for short message
    public function getShortMessageAttribute()
    {
        if ($this->mode === 'manual') {
            return 'Relay Control';
        }
        return Str::limit($this->message, 30);
    }
}