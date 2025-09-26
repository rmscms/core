<?php

namespace RMS\Core\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use RMS\Core\Models\Notification;
use RMS\Core\Models\NotificationDelivery;

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $notificationId;

    public function __construct(int $notificationId)
    {
        $this->notificationId = $notificationId;
        $this->onQueue(config('rms.notifications.queue', 'default'));
    }

    public function handle(): void
    {
        $notification = Notification::query()->with('deliveries')->find($this->notificationId);
        if (!$notification) {
            return;
        }

        foreach ($notification->deliveries as $delivery) {
            if ($delivery->status === 'sent' || $delivery->status === 'canceled') {
                continue;
            }

            // Respect disabled push channel
            if ($delivery->channel === 'push' && !config('rms.notifications.channels.push.enabled', false)) {
                $delivery->status = 'canceled';
                $delivery->save();
                continue;
            }

            $delivery->attempts++;
            $delivery->last_attempt_at = now();

            try {
                // TODO: implement channel drivers (email/telegram/webhook) in follow-up
                // For now, we mark as sent to validate pipeline
                $delivery->status = 'sent';
                $delivery->sent_at = now();
                $delivery->last_error = null;
            } catch (\Throwable $e) {
                $delivery->status = 'failed';
                $delivery->last_error = $e->getMessage();
            }

            $delivery->save();
        }

        // Update notification aggregate status
        $statuses = $notification->deliveries->pluck('status');
        if ($statuses->every(fn ($s) => $s === 'sent')) {
            $notification->status = 'sent';
        } elseif ($statuses->contains('sent')) {
            $notification->status = 'partially_sent';
        } elseif ($statuses->every(fn ($s) => $s === 'canceled')) {
            $notification->status = 'canceled';
        } elseif ($statuses->every(fn ($s) => $s === 'failed')) {
            $notification->status = 'failed';
        }
        $notification->save();
    }
}
