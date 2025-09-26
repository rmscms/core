<?php

namespace RMS\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationDelivery extends Model
{
    protected $table = 'rms_notification_deliveries';

    protected $fillable = [
        'notification_id', 'channel', 'status', 'attempts', 'last_error', 'last_attempt_at', 'sent_at', 'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'last_attempt_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function notification(): BelongsTo
    {
        return $this->belongsTo(Notification::class, 'notification_id');
    }
}
