<?php

namespace RMS\Core\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

/**
 * Install the RMS CMS package.
 */
class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rms:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the RMS CMS package';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $this->info('Starting RMS CMS installation...');

        $results = [
            $this->publishConfig(),
            $this->publishAdminAssets(),
            $this->publishFrontAssets(),
            $this->publishAdminViews(),
            $this->publishTranslations(),
            $this->runMigrations(),
            $this->runSeeder(),
        ];

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

        $this->info('RMS CMS installed successfully!');
        $this->line('You can login with the following credentials:');
        $this->line('Email: ' . config('cms.default_admin_email', 'admin@example.com'));
        $this->line('Password: ' . config('cms.default_admin_password', 'password123'));
        $this->line('Login URL: ' . url(config('cms.admin_url', 'admin') . '/login'));
        $this->line('Please change the default password after login.');
    }

    /**
     * Publish the configuration file.
     *
     * @return array
     */
    protected function publishConfig(): array
    {
        try {
            Artisan::call('vendor:publish', ['--tag' => 'cms-config']);
            return ['step' => 'Publish Config', 'status' => true, 'message' => 'Published config/cms.php'];
        } catch (\Exception $e) {
            return ['step' => 'Publish Config', 'status' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Publish admin assets.
     *
     * @return array
     */
    protected function publishAdminAssets(): array
    {
        try {
            Artisan::call('vendor:publish', ['--tag' => 'cms-admin-assets']);
            return ['step' => 'Publish Admin Assets', 'status' => true, 'message' => 'Published to public/' . config('cms.admin_theme', 'admin')];
        } catch (\Exception $e) {
            return ['step' => 'Publish Admin Assets', 'status' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Publish front-end assets.
     *
     * @return array
     */
    protected function publishFrontAssets(): array
    {
        try {
            Artisan::call('vendor:publish', ['--tag' => 'cms-front-assets']);
            return ['step' => 'Publish Front Assets', 'status' => true, 'message' => 'Published to public/' . config('cms.front_theme', 'panel')];
        } catch (\Exception $e) {
            return ['step' => 'Publish Front Assets', 'status' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Publish admin views.
     *
     * @return array
     */
    protected function publishAdminViews(): array
    {
        try {
            Artisan::call('vendor:publish', ['--tag' => 'cms-admin-views']);
            return ['step' => 'Publish Admin Views', 'status' => true, 'message' => 'Published to resources/views/vendor/cms'];
        } catch (\Exception $e) {
            return ['step' => 'Publish Admin Views', 'status' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Publish translations.
     *
     * @return array
     */
    protected function publishTranslations(): array
    {
        try {
            Artisan::call('vendor:publish', ['--tag' => 'cms-translations']);
            return ['step' => 'Publish Translations', 'status' => true, 'message' => 'Published to resources/lang'];
        } catch (\Exception $e) {
            return ['step' => 'Publish Translations', 'status' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Run migrations.
     *
     * @return array
     */
    protected function runMigrations(): array
    {
        try {
            Artisan::call('migrate');
            return ['step' => 'Run Migrations', 'status' => true, 'message' => 'Ran migrations for RMS CMS'];
        } catch (\Exception $e) {
            return ['step' => 'Run Migrations', 'status' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Run admin seeder.
     *
     * @return array
     */
    protected function runSeeder(): array
    {
        try {
            Artisan::call('db:seed', ['--class' => \RMS\Core\Database\Seeders\AdminSeeder::class]);
            return ['step' => 'Run Seeder', 'status' => true, 'message' => 'Seeded default admin'];
        } catch (\Exception $e) {
            return ['step' => 'Run Seeder', 'status' => false, 'message' => $e->getMessage()];
        }
    }
}
