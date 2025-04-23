<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    protected $primaryKey = 'id';
    public $incrementing = false;

    protected $fillable = [
        'rtc',
        'dfplayer',
        'rtc_time',
        'last_communication',
        'last_sync',
        'status'
    ];

    protected $casts = [
        'rtc' => 'boolean',
        'dfplayer' => 'boolean',
        'rtc_time' => 'datetime',  // Cast ke datetime
        'last_communication' => 'datetime',
        'last_sync' => 'datetime'
    ];

    /**
     * Get system status singleton
     */
    public static function systemStatus()
    {
        return static::firstOrCreate(['id' => 1], [
            'rtc' => false,
            'dfplayer' => false
        ]);
    }
}
