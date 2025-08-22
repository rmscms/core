<?php

namespace RMS\Core\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class InstallCommand extends Command
{
    protected $signature = 'rms:install';
    protected $description = 'Install the RMS CMS package';

    public function handle()
    {
        $this->info('Starting RMS CMS installation...');

        $results = [];

        // انتشار فایل تنظیمات
        $results[] = $this->publishConfig();
        // انتشار assets ادمین
        $results[] = $this->publishAdminAssets();
        // انتشار assets کاربری
        $results[] = $this->publishFrontAssets();
        // انتشار ویوهای ادمین
        $results[] = $this->publishAdminViews();
        // اجرای مهاجرت‌ها
        $results[] = $this->runMigrations();
        // اجرای Seeder
        $results[] = $this->runSeeder();

        // نمایش نتایج به صورت جدول
        $this->table(
            ['Step', 'Status', 'Message'],
            array_map(function ($result) {
                return [
                    $result['step'],
                    $result['status'] ? '<fg=green>Success</>' : '<fg=red>Failed</>',
                    $result['message'],
                ];
            }, $results)
        );

        // نمایش یوزر و پسورد
        $this->info('RMS CMS installed successfully!');
        $this->line('You can login with the following credentials:');
        $this->line('Email: admin@example.com');
        $this->line('Password: password123');
        $this->line('Login URL: ' . url(config('cms.admin_url') . '/login'));
        $this->line('Please change the default password after login.');
    }

    protected function publishConfig()
    {
        try {
            Artisan::call('vendor:publish', ['--tag' => 'cms-config']);
            return ['step' => 'Publish Config', 'status' => true, 'message' => 'Published config/cms.php'];
        } catch (\Exception $e) {
            return ['step' => 'Publish Config', 'status' => false, 'message' => $e->getMessage()];
        }
    }

    protected function publishAdminAssets()
    {
        try {
            Artisan::call('vendor:publish', ['--tag' => 'cms-admin-assets']);
            return ['step' => 'Publish Admin Assets', 'status' => true, 'message' => 'Published to public/' . config('cms.admin_theme')];
        } catch (\Exception $e) {
            return ['step' => 'Publish Admin Assets', 'status' => false, 'message' => $e->getMessage()];
        }
    }

    protected function publishFrontAssets()
    {
        try {
            Artisan::call('vendor:publish', ['--tag' => 'cms-front-assets']);
            return ['step' => 'Publish Front Assets', 'status' => true, 'message' => 'Published to public/' . config('cms.front_theme')];
        } catch (\Exception $e) {
            return ['step' => 'Publish Front Assets', 'status' => false, 'message' => $e->getMessage()];
        }
    }

    protected function publishAdminViews()
    {
        try {
            Artisan::call('vendor:publish', ['--tag' => 'cms-admin-views']);
            return ['step' => 'Publish Admin Views', 'status' => true, 'message' => 'Published to resources/views/' . config('cms.admin_theme')];
        } catch (\Exception $e) {
            return ['step' => 'Publish Admin Views', 'status' => false, 'message' => $e->getMessage()];
        }
    }

    protected function runMigrations()
    {
        try {
            Artisan::call('migrate');
            return ['step' => 'Run Migrations', 'status' => true, 'message' => 'Ran migrations for RMS CMS'];
        } catch (\Exception $e) {
            return ['step' => 'Run Migrations', 'status' => false, 'message' => $e->getMessage()];
        }
    }

    protected function runSeeder()
    {
        try {
            Artisan::call('db:seed', ['--class' => \RMS\Core\Database\Seeders\AdminSeeder::class]);
            return ['step' => 'Run Seeder', 'status' => true, 'message' => 'Seeded default admin'];
        } catch (\Exception $e) {
            return ['step' => 'Run Seeder', 'status' => false, 'message' => $e->getMessage()];
        }
    }
}
