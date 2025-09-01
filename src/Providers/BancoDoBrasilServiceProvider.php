<?php

namespace Accordous\BbClient\Providers;

use Illuminate\Support\ServiceProvider;
use Accordous\BbClient\Http\BancoDoBrasilClient;

class BancoDoBrasilServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge config from file only if it exists
        $configPath = __DIR__ . '/../../config/banco-do-brasil.php';
        if (file_exists($configPath)) {
            $this->mergeConfigFrom($configPath, 'banco-do-brasil');
        }

        $this->app->singleton(BancoDoBrasilClient::class, function ($app) {
            return new BancoDoBrasilClient(config('banco-do-brasil'));
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $configPath = __DIR__ . '/../../config/banco-do-brasil.php';
        if (file_exists($configPath)) {
            $this->publishes([
                $configPath => config_path('banco-do-brasil.php'),
            ], 'config');
        }
    }
}