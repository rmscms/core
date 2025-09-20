<?php

namespace RMS\Core;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Auth\AuthenticationException;
use RMS\Core\Validation\CustomValidationRules;

class CoreServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Register admin authentication middleware
        $this->registerAdminMiddleware();

        $this->publishes([
            __DIR__.'/../config/cms.php' => config_path('cms.php'),
        ], 'cms-config');
        
        $this->publishes([
            __DIR__.'/../config/plugins.php' => config_path('plugins.php'),
        ], 'cms-plugins-config');

        $this->publishes([
            __DIR__.'/../assets/' => public_path(config('cms.admin_theme')),
        ], 'cms-admin-assets');

        $this->publishes([
            __DIR__.'/../assets/' => public_path(config('cms.front_theme')),
        ], 'cms-front-assets');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/cms'),
        ], 'cms-admin-views');

        $this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang'),
        ], 'cms-translations');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'cms');

        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'cms');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->loadRoutesFrom(__DIR__.'/../routes/admin.php');

        $this->commands([
            \RMS\Core\Console\InstallCommand::class,
        ]);

        // Register custom Blade directives
        $this->registerBladeDirectives();

        // Register anonymous components
        $this->registerAnonymousComponents();

        // Register custom validation rules
        $this->registerCustomValidationRules();

        // Initialize global query logging for debug
        $this->initializeGlobalQueryLogging();
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/cms.php', 'cms');

        config([
            'auth.guards' => array_merge(config('auth.guards', []), config('cms.auth.guards', [])),
            'auth.providers' => array_merge(config('auth.providers', []), config('cms.auth.providers', [])),
        ]);

        // Register admin authentication services
        $this->app->singleton(\RMS\Core\Services\AdminRateLimiter::class);
        $this->app->singleton(\RMS\Core\Services\AdminAuthService::class);
    }

    /**
     * Register custom Blade directives for RMS CMS.
     *
     * @return void
     */
    protected function registerBladeDirectives(): void
    {
        // @assign directive for variable assignment
        Blade::directive('assign', function ($expression) {
            // Parse expression like: 'property', $user->id + 1
            $parts = explode(',', $expression, 2);
            if (count($parts) !== 2) {
                throw new \InvalidArgumentException('assign directive requires exactly 2 parameters: variable name and value');
            }

            $variable = trim($parts[0], "'\" ");
            $value = trim($parts[1]);

            return "<?php \${$variable} = {$value}; ?>";
        });

        // @asset directive for theme-aware asset loading
        Blade::directive('asset', function ($expression) {
            $path = trim($expression, "'\" ");
            return "<?php echo asset(config('cms.admin_theme') . '/{$path}'); ?>";
        });

        // @persian_date directive for easy Persian date formatting
        Blade::directive('persian_date', function ($expression) {
            $parts = explode(',', $expression, 2);
            $date = trim($parts[0]);
            $format = isset($parts[1]) ? trim($parts[1], "'\" ") : 'Y/m/d H:i:s';

            return "<?php echo \\RMS\\Helper\\persian_date({$date}, '{$format}'); ?>";
        });

        // @money directive for Persian currency formatting
        Blade::directive('money', function ($expression) {
            $parts = explode(',', $expression, 2);
            $amount = trim($parts[0]);
            $currency = isset($parts[1]) ? trim($parts[1], "'\" ") : null;

            $currencyParam = $currency ? "'{$currency}'" : 'null';
            return "<?php echo \\RMS\\Helper\\displayAmount({$amount}, {$currencyParam}); ?>";
        });

        // @persian_number directive for converting numbers to Persian
        Blade::directive('persian_number', function ($expression) {
            return "<?php echo str_replace(['0','1','2','3','4','5','6','7','8','9'], ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'], {$expression}); ?>";
        });
    }

    /**
     * Register custom validation rules for RMS CMS.
     *
     * @return void
     */
    protected function registerCustomValidationRules()
    {
        // Add custom validation rules if needed
    }

    /**
     * Register anonymous Blade components for the package
     */
    protected function registerAnonymousComponents()
    {
        // Register anonymous components from the components directory
        \Illuminate\Support\Facades\Blade::anonymousComponentPath(
            __DIR__ . '/../resources/views/components',
            'cms'
        );
    }

    /**
     * Register admin authentication middleware.
     *
     * @return void
     */
    protected function registerAdminMiddleware(): void
    {
        $router = $this->app['router'];

        // Register the admin authentication middleware
        $router->aliasMiddleware('auth.admin', \RMS\Core\Middleware\AdminAuthenticate::class);
    }

    /**
     * Initialize global query logging for debug system
     */
    protected function initializeGlobalQueryLogging(): void
    {
        // فقط اگر debug فعال باشد
        if (!config('app.debug', false) || !config('rms.debug.enabled', true)) {
            return;
        }

        // فعال کردن query logging
        DB::enableQueryLog();

        // ایجاد یک collector سراسری برای queries
        if (!$this->app->bound('rms.debug.queries')) {
            $this->app->instance('rms.debug.queries', []);
        }

        // listener برای جمع‌آوری همه queries
        DB::listen(function ($query) {
            $queries = app('rms.debug.queries');
            $queries[] = [
                'sql' => $query->sql,
                'query' => $query->sql, // برای سازگاری با JS
                'bindings' => $query->bindings,
                'time' => $query->time,
                'timestamp' => now()->toISOString(),
                'formatted_query' => $this->formatQueryWithBindings($query->sql, $query->bindings)
            ];
            app()->instance('rms.debug.queries', $queries);
        });
    }

    /**
     * Format query with bindings
     */
    private function formatQueryWithBindings(string $sql, array $bindings): string
    {
        foreach ($bindings as $binding) {
            if (is_string($binding)) {
                $binding = "'" . $binding . "'";
            } elseif (is_null($binding)) {
                $binding = 'NULL';
            } elseif (is_bool($binding)) {
                $binding = $binding ? 'TRUE' : 'FALSE';
            }

            $sql = preg_replace('/\?/', $binding, $sql, 1);
        }

        return $sql;
    }
}
