<?php

declare(strict_types=1);

namespace RMS\Core\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Helper\ProgressBar;

/**
 * Enhanced RMS CMS installation command with modern Laravel 12 features.
 * 
 * دستور نصب سیستم مدیریت محتوای RMS با قابلیت‌های پیشرفته
 * 
 * @package RMS\Core\Console
 */
class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'rms:install 
                            {--force : Force installation even if already installed}
                            {--no-seed : Skip seeding default data}
                            {--no-migrate : Skip running migrations}';

    /**
     * The console command description.
     */
    protected $description = 'Install the RMS CMS package with all necessary components';

    /**
     * Installation steps configuration.
     */
    protected array $installationSteps = [
        'publishConfig' => 'انتشار فایل‌های پیکربندی',
        'publishAdminAssets' => 'انتشار فایل‌های ادمین',
        'publishFrontAssets' => 'انتشار فایل‌های فرانت',
        'publishAdminViews' => 'انتشار قالب‌های ادمین',
        'publishTranslations' => 'انتشار فایل‌های ترجمه',
        'runMigrations' => 'اجرای migration ها',
        'runSeeder' => 'ایجاد داده‌های پایه'
    ];

    /**
     * Installation results storage.
     */
    protected array $results = [];

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        try {
            $this->displayWelcomeBanner();
            
            if (!$this->option('force') && $this->isAlreadyInstalled()) {
                $this->warn('⚠️  RMS CMS appears to be already installed!');
                
                if (!$this->confirm('Do you want to continue anyway?', false)) {
                    $this->info('Installation cancelled.');
                    return Command::SUCCESS;
                }
            }

            $this->runInstallation();
            $this->displayResults();
            $this->displaySuccessMessage();
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Installation failed: ' . $e->getMessage());
            Log::error('RMS installation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return Command::FAILURE;
        }
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
     * Run migrations with enhanced error handling.
     *
     * @return array
     */
    protected function runMigrations(): array
    {
        if ($this->option('no-migrate')) {
            return ['step' => 'Run Migrations', 'status' => true, 'message' => 'Skipped (--no-migrate option)'];
        }
        
        try {
            $exitCode = Artisan::call('migrate', ['--force' => true]);
            
            if ($exitCode === 0) {
                return ['step' => 'Run Migrations', 'status' => true, 'message' => 'Successfully ran all migrations'];
            } else {
                return ['step' => 'Run Migrations', 'status' => false, 'message' => 'Migration completed with warnings'];
            }
        } catch (\Exception $e) {
            Log::error('Migration failed during installation', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return ['step' => 'Run Migrations', 'status' => false, 'message' => 'Migration failed: ' . $e->getMessage()];
        }
    }

    /**
     * Run admin seeder.
     *
     * @return array
     */
    protected function runSeeder(): array
    {
        if ($this->option('no-seed')) {
            return ['step' => 'Run Seeder', 'status' => true, 'message' => 'Skipped (--no-seed option)'];
        }
        
        try {
            Artisan::call('db:seed', ['--class' => \RMS\Core\Database\Seeders\AdminSeeder::class]);
            return ['step' => 'Run Seeder', 'status' => true, 'message' => 'Seeded default admin'];
        } catch (\Exception $e) {
            return ['step' => 'Run Seeder', 'status' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Display welcome banner with ASCII art.
     *
     * @return void
     */
    protected function displayWelcomeBanner(): void
    {
        $this->newLine();
        $this->line('<fg=blue>╔══════════════════════════════════════════════════════════╗</>');        
        $this->line('<fg=blue>║</><fg=white;bg=blue>                     RMS CMS INSTALLER                     </><fg=blue>║</>');        
        $this->line('<fg=blue>╚══════════════════════════════════════════════════════════╝</>');        
        $this->newLine();
        $this->info('🚀 Starting RMS CMS installation process...');
        $this->line('   This will set up all required files and database tables');
        $this->newLine();
    }
    
    /**
     * Check if RMS CMS is already installed.
     *
     * @return bool
     */
    protected function isAlreadyInstalled(): bool
    {
        // Check if config file exists
        $configExists = file_exists(config_path('cms.php'));
        
        // Check if admin assets exist
        $adminTheme = config('cms.admin_theme', 'admin');
        $adminAssetsExist = is_dir(public_path($adminTheme));
        
        // Check if admin user exists in database
        $adminExists = false;
        try {
            // A safer approach without directly accessing the database
            $adminExists = Artisan::call('db:seed', [
                '--class' => \RMS\Core\Database\Seeders\AdminSeeder::class,
                '--pretend' => true
            ]) === 0;
        } catch (\Exception $e) {
            // Ignore errors during check
        }
        
        return $configExists && $adminAssetsExist && $adminExists;
    }
    
    /**
     * Run the installation process with progress display.
     *
     * @return void
     */
    protected function runInstallation(): void
    {
        $totalSteps = count($this->installationSteps);
        $currentStep = 1;
        
        $this->newLine();
        $this->line('<fg=yellow>📋 Installing RMS CMS components:</>');
        
        foreach ($this->installationSteps as $method => $description) {
            // Skip migrations if option is set
            if ($method === 'runMigrations' && $this->option('no-migrate')) {
                $this->results[] = ['step' => 'Run Migrations', 'status' => true, 'message' => 'Skipped (--no-migrate option)'];
                continue;
            }
            
            $this->output->write("   <fg=yellow>[{$currentStep}/{$totalSteps}]</> {$description}... ");
            
            // Call the installation method
            $result = $this->$method();
            $this->results[] = $result;
            
            // Display result
            if ($result['status']) {
                $this->output->writeln('<fg=green>✓ Success</>');
            } else {
                $this->output->writeln('<fg=red>✗ Failed</>');
            }
            
            $currentStep++;
        }
        
        $this->newLine();
    }
    
    /**
     * Display installation results in a table.
     *
     * @return void
     */
    protected function displayResults(): void
    {
        $this->line('<fg=yellow>📊 Installation Summary:</>');
        
        $this->table(
            ['Step', 'Status', 'Message'],
            array_map(function ($result) {
                return [
                    $result['step'],
                    $result['status'] 
                        ? '<fg=green;options=bold>✓ SUCCESS</>' 
                        : '<fg=red;options=bold>✗ FAILED</>',
                    $result['message'],
                ];
            }, $this->results)
        );
    }
    
    /**
     * Display success message with login information.
     *
     * @return void
     */
    protected function displaySuccessMessage(): void
    {
        // Check if any steps failed
        $hasFailures = collect($this->results)->contains('status', false);
        
        if ($hasFailures) {
            $this->warn('⚠️  RMS CMS installation completed with some errors.');
            $this->warn('   Please check the table above for details.');
            return;
        }
        
        $this->newLine();
        $this->line('<fg=green>╔══════════════════════════════════════════════════════════╗</>');        
        $this->line('<fg=green>║</><fg=white;bg=green>               INSTALLATION SUCCESSFUL!                 </><fg=green>║</>');        
        $this->line('<fg=green>╚══════════════════════════════════════════════════════════╝</>');        
        $this->newLine();
        
        $this->info('🔑 Admin Login Credentials:');
        $this->line('   <fg=yellow>Email:</> ' . config('cms.default_admin_email', 'admin@example.com'));
        $this->line('   <fg=yellow>Password:</> ' . config('cms.default_admin_password', 'password123'));
        $this->line('   <fg=yellow>Login URL:</> ' . url(config('cms.admin_url', 'admin') . '/login'));
        $this->line('   <fg=red;options=bold>⚠️  Please change the default password after login!</>');
        
        $this->newLine();
        $this->info('📘 Next steps:');
        $this->line('   1. Configure your application in <fg=yellow>config/cms.php</>');
        $this->line('   2. Customize the admin theme in <fg=yellow>public/' . config('cms.admin_theme', 'admin') . '</>');
        $this->line('   3. Start building your modules and content types');
        $this->newLine();
    }
}
