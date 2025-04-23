<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    /**
     * Relationship with Announcement (Many-to-Many)
     */
    public function announcements()
    {
        return $this->belongsToMany(Announcement::class, 'announcement_room', 'room_name', 'announcement_id');
    }
}