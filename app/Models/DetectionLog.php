<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetectionLog extends Model
{
    protected $table = 'detection_logs';
    
    public $timestamps = false; // Kita pakai detected_at manual

    protected $fillable = [
        'device_id',
        'student_id',
        'student_name',
        'nis',
        'probability',
        'detected_at',
    ];

    protected $casts = [
        'detected_at' => 'datetime',
        'probability' => 'decimal:4',
    ];

    /**
     * Relasi dengan Device
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(Devices::class, 'device_id');
    }
}
