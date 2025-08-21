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

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
