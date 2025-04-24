<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BellHistory extends Model
{
    use HasFactory;

    const TRIGGER_SCHEDULE = 'schedule';
    const TRIGGER_MANUAL = 'manual';

    protected $fillable = [
        'hari',
        'waktu',
        'file_number',
        'trigger_type',
        'ring_time',
        'volume',
        'repeat'
    ];

    protected $casts = [
        'ring_time' => 'datetime',
        'volume' => 'integer',
        'repeat' => 'integer'
    ];

    public static function getTriggerTypes()
    {
        return [
            self::TRIGGER_SCHEDULE => 'Jadwal',
            self::TRIGGER_MANUAL => 'Manual'
        ];
    }
}