<?php

namespace RMS\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Queue;
use RMS\Core\Jobs\SendNotificationJob;
use RMS\Core\Models\Notification;
use RMS\Core\Models\NotificationSchedule;
use Carbon\Carbon;

class ProcessDueReminders extends Command
{
    protected $signature = 'rms:notifications:process-due';
    protected $description = 'Dispatch due notification reminders (one-off and recurring)';

    public function handle(): int
    {
        $now = Carbon::now('UTC');

        $due = NotificationSchedule::query()
            ->where('status', 'active')
            ->whereNotNull('next_run_at')
            ->where('next_run_at', '<=', $now)
            ->with('notification.deliveries')
            ->limit(200)
            ->get();

        foreach ($due as $schedule) {
            $notification = $schedule->notification;
            if (!$notification) {
                $schedule->status = 'canceled';
                $schedule->save();
                continue;
            }

            Queue::push(new SendNotificationJob($notification->id));

            // Compute next occurrence; if none, mark completed
            $recurrence = $schedule->recurrence ?: null;
            $tz = $schedule->timezone ?: config('app.timezone');
$service = app(\RMS\Core\Services\NotificationsService::class);
            $next = $service->computeNextRunAt(null, $recurrence, $tz);

            if ($next) {
                $schedule->next_run_at = $next;
            } else {
                $schedule->status = 'completed';
                $schedule->next_run_at = null;
            }
            $schedule->save();
        }

        return self::SUCCESS;
    }
}
