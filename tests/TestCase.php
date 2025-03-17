<?php

namespace BancoDoBrasil\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use BancoDoBrasil\BancoDoBrasilServiceProvider;

class TestCase extends Orchestra
{
    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            BancoDoBrasilServiceProvider::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function defineEnvironment($app): void
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        // Set Banco do Brasil API configuration for testing
        $app['config']->set('banco-do-brasil', [
            'base_url' => env('BB_API_BASE_URL', 'https://api.hm.bb.com.br'),
            'oauth_url' => env('BB_OAUTH_URL', 'https://oauth.hm.bb.com.br'),
            'client_id' => env('BB_CLIENT_ID', 'test-client-id'),
            'client_secret' => env('BB_CLIENT_SECRET', 'test-client-secret'),
            'developer_application_key' => env('BB_DEVELOPER_APPLICATION_KEY', 'test-developer-key'),
            'cobranca' => [
                'gw_app_key' => env('BB_GW_APP_KEY', 'test-gw-app-key'),
            ],
            'timeout' => env('BB_API_TIMEOUT', 30),
            'connect_timeout' => env('BB_API_CONNECT_TIMEOUT', 10),
        ]);
    }
} 