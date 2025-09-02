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
            $config = config('banco-do-brasil', []);
            return new BancoDoBrasilClient(
                $config['client_id'] ?? null,
                $config['client_secret'] ?? null,
                $config['developer_application_key'] ?? null,
                $config['convenio'] ?? null
            );
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