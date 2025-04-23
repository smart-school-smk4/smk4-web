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
        'target_rooms',
        'duration',
        'sent_at',
        'is_active',
        'status'
    ];

    protected $casts = [
        'target_rooms' => 'array', // Kolom ini akan disimpan sebagai JSON
        'sent_at' => 'datetime',
        'is_active' => 'boolean'
    ];

    /**
     * Relationship with Room (Many-to-Many)
     */
    public function rooms()
    {
        return $this->belongsToMany(Room::class, 'announcement_room', 'announcement_id', 'room_name');
    }
}