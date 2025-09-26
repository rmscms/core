<?php

namespace RMS\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Notification extends Model
{
    protected $table = 'rms_notifications';

    protected $fillable = [
        'notifiable_type', 'notifiable_id', 'account_id',
        'category', 'title', 'message', 'meta',
        'read_at', 'deliver_at', 'is_broadcast', 'status', 'idempotency_key',
    ];

    protected $casts = [
        'meta' => 'array',
        'read_at' => 'datetime',
        'deliver_at' => 'datetime',
        'is_broadcast' => 'boolean',
    ];

    public function deliveries(): HasMany
    {
        return $this->hasMany(NotificationDelivery::class, 'notification_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(NotificationSchedule::class, 'notification_id');
    }
}
