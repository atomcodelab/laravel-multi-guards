<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class MultiGuardsProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Add guard
        $this->app->config['auth.guards.admin'] = [
            'driver' => 'session',
            'provider' => 'admins',
        ];

        // Add provider
        $this->app->config['auth.providers.admins'] = [
            'driver' => 'eloquent',
            'model' => \App\Models\Admin::class,
        ];
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
