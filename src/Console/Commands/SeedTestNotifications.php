<?php

namespace RMS\Core\Console\Commands;

use Illuminate\Console\Command;
use RMS\Core\Services\NotificationsService;

class SeedTestNotifications extends Command
{
    protected $signature = 'rms:notifications:test-seed {--admin=1} {--count=3}';
    protected $description = 'Seed a few sample notifications for an admin to test UI integration';

    public function handle(NotificationsService $service): int
    {
        $adminId = (int)$this->option('admin');
        $count = max(1, (int)$this->option('count'));

        for ($i = 1; $i <= $count; $i++) {
            $service->sendNow([
'notifiable_type' => \RMS\Core\Models\Admin::class,
                'notifiable_id' => $adminId,
                'category' => 'info',
                'title' => 'Test Notification #' . $i,
                'message' => 'This is a seeded test notification for admin #' . $adminId,
            ], [
                // Keep channels empty for UI test (in-app list). Delivery job will mark queued rows as sent if any.
            ]);
        }

        $this->info("Seeded {$count} notifications for admin #{$adminId}.");
        return self::SUCCESS;
    }
}
