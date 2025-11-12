<?php

namespace RMS\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class PublishAdminControllerStub extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'rms:publish-admin-controller {--force : Overwrite the existing AdminController stub if it already exists}';

    /**
     * The console command description.
     */
    protected $description = 'Publish the App\\Http\\Controllers\\Admin\\AdminController stub from the RMS core package.';

    public function handle(Filesystem $filesystem): int
    {
        $stubPath = dirname(__DIR__, 3) . '/stubs/Admin/AdminController.stub';
        $targetPath = app_path('Http/Controllers/Admin/AdminController.php');

        if (!$filesystem->exists($stubPath)) {
            $this->error('AdminController stub file is missing inside the RMS core package.');
            return self::FAILURE;
        }

        $filesystem->ensureDirectoryExists(dirname($targetPath));

        if ($filesystem->exists($targetPath) && !$this->option('force')) {
            $this->info('AdminController already exists. Use --force to overwrite.');
            return self::SUCCESS;
        }

        try {
            $filesystem->put($targetPath, $filesystem->get($stubPath));
        } catch (\Throwable $throwable) {
            $this->error('Failed to publish AdminController stub: ' . $throwable->getMessage());
            return self::FAILURE;
        }

        $relativePath = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $targetPath);
        $this->info(sprintf('AdminController stub published to %s', $relativePath));

        return self::SUCCESS;
    }
}
