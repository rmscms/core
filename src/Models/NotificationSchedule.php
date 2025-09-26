<?php

namespace RMS\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationSchedule extends Model
{
    protected $table = 'rms_notification_schedules';

    protected $fillable = [
        'notification_id', 'deliver_at', 'recurrence', 'next_run_at', 'timezone', 'status',
    ];

    protected $casts = [
        'deliver_at' => 'datetime',
        'next_run_at' => 'datetime',
        'recurrence' => 'array',
    ];

    public function notification(): BelongsTo
    {
        return $this->belongsTo(Notification::class, 'notification_id');
    }
}
