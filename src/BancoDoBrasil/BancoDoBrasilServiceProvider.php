<?php

namespace BancoDoBrasil;

use Illuminate\Support\ServiceProvider;
use BancoDoBrasil\Http\BancoDoBrasilClient;

class BancoDoBrasilServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
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
     *
     * @return void
     */
    public function boot()
    {
        $configPath = __DIR__ . '/../../config/banco-do-brasil.php';
        if (file_exists($configPath)) {
            $this->publishes([
                $configPath => config_path('banco-do-brasil.php'),
            ], 'config');
        }
    }
} 