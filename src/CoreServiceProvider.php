<?php

namespace RMS\Core;

use Illuminate\Support\ServiceProvider;

class CoreServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/cms.php' => config_path('cms.php'),
        ], 'cms-config');

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
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/cms.php', 'cms');

        config([
            'auth.guards' => array_merge(config('auth.guards', []), config('cms.auth.guards', [])),
            'auth.providers' => array_merge(config('auth.providers', []), config('cms.auth.providers', [])),
        ]);
    }
}
