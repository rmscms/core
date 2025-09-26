<?php

namespace RMS\Core\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Queue;
use RMS\Core\Models\Notification;
use RMS\Core\Models\NotificationDelivery;
use RMS\Core\Models\NotificationSchedule;
use RMS\Core\Jobs\SendNotificationJob;

class NotificationsService
{
    // Default: push channel disabled via config
    public function sendNow(array $data, array $channels = []): Notification
    {
        // Create notification
        $notification = Notification::create([
            'notifiable_type' => $data['notifiable_type'] ?? null,
            'notifiable_id'   => $data['notifiable_id'] ?? null,
            'account_id'      => $data['account_id'] ?? null,
            'category'        => $data['category'] ?? 'general',
            'title'           => $data['title'] ?? null,
            'message'         => $data['message'],
            'meta'            => $data['meta'] ?? null,
            'is_broadcast'    => (bool)($data['is_broadcast'] ?? false),
            'status'          => 'created',
            'idempotency_key' => $data['idempotency_key'] ?? null,
        ]);

        // Create delivery rows per-channel
        foreach ($channels as $channel => $meta) {
            NotificationDelivery::create([
                'notification_id' => $notification->id,
                'channel' => $channel,
                'status' => 'queued',
                'meta' => is_array($meta) ? $meta : null,
            ]);
        }

        // Dispatch job now
        Queue::push(new SendNotificationJob($notification->id));

        return $notification;
    }

    // Schedule one-off or recurring delivery
    public function schedule(array $data, array $channels = [], array $schedule = []): Notification
    {
        $deliverAt = $schedule['deliver_at'] ?? null; // datetime string or Carbon
        $recurrence = $schedule['recurrence'] ?? null; // e.g., ['type' => 'weekly','weekday' => 1,'time' => '09:30']
        $timezone = $schedule['timezone'] ?? config('app.timezone');

        $notification = Notification::create([
            'notifiable_type' => $data['notifiable_type'] ?? null,
            'notifiable_id'   => $data['notifiable_id'] ?? null,
            'account_id'      => $data['account_id'] ?? null,
            'category'        => $data['category'] ?? 'general',
            'title'           => $data['title'] ?? null,
            'message'         => $data['message'],
            'meta'            => $data['meta'] ?? null,
            'is_broadcast'    => (bool)($data['is_broadcast'] ?? false),
            'status'          => 'scheduled',
            'deliver_at'      => $deliverAt ? Carbon::parse($deliverAt, $timezone)->utc() : null,
            'idempotency_key' => $data['idempotency_key'] ?? null,
        ]);

        foreach ($channels as $channel => $meta) {
            NotificationDelivery::create([
                'notification_id' => $notification->id,
                'channel' => $channel,
                'status' => 'pending',
                'meta' => is_array($meta) ? $meta : null,
            ]);
        }

        $nextRunAt = $this->computeNextRunAt($deliverAt, $recurrence, $timezone);

        NotificationSchedule::create([
            'notification_id' => $notification->id,
            'deliver_at' => $deliverAt ? Carbon::parse($deliverAt, $timezone)->utc() : null,
            'recurrence' => $recurrence,
            'next_run_at' => $nextRunAt,
            'timezone' => $timezone,
            'status' => 'active',
        ]);

        return $notification;
    }

    public function cancelSchedule(int $notificationId): bool
    {
        return NotificationSchedule::where('notification_id', $notificationId)
            ->update(['status' => 'canceled']) > 0;
    }

    // Compute next_run_at for one-off/weekly/monthly
    public function computeNextRunAt($deliverAt, $recurrence, string $timezone): ?Carbon
    {
        if ($recurrence === null) {
            return $deliverAt ? Carbon::parse($deliverAt, $timezone)->utc() : null;
        }

        $now = Carbon::now($timezone);
        $type = $recurrence['type'] ?? null;

        if ($type === 'weekly') {
            // weekday: 0=Sunday..6=Saturday, time: HH:MM
            $weekday = (int)($recurrence['weekday'] ?? 1);
            [$h, $m] = explode(':', $recurrence['time'] ?? '09:00');
            $next = $now->copy()->next($weekday === 0 ? 7 : $weekday + 1)->setTime((int)$h, (int)$m);
            if ($next->lessThanOrEqualTo($now)) {
                $next->addWeek();
            }
            return $next->utc();
        }

        if ($type === 'monthly') {
            // day: 1..28/29/30/31, time: HH:MM
            $day = (int)($recurrence['day'] ?? 1);
            [$h, $m] = explode(':', $recurrence['time'] ?? '09:00');
            $next = $now->copy()->setTime((int)$h, (int)$m)->day(min($day, $now->daysInMonth));
            if ($next->lessThanOrEqualTo($now)) {
                $next = $now->copy()->addMonthNoOverflow()->setTime((int)$h, (int)$m)->day(min($day, $now->copy()->addMonthNoOverflow()->daysInMonth));
            }
            return $next->utc();
        }

        // default fallback
        return $deliverAt ? Carbon::parse($deliverAt, $timezone)->utc() : null;
    }
}
