<?php

namespace Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Accordous\BbClient\Providers\BancoDoBrasilServiceProvider;

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

        // Load .env variables directly from file - try multiple paths
        $possiblePaths = [
            __DIR__ . '/../.env',           // Relative to tests directory
            getcwd() . '/.env',             // Current working directory
            realpath(__DIR__ . '/../') . '/.env'  // Absolute path
        ];

        $envVars = [];
        foreach ($possiblePaths as $envFile) {
            if (file_exists($envFile)) {
                $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach ($lines as $line) {
                    if (strpos($line, '=') !== false && !str_starts_with($line, '#')) {
                        [$key, $value] = explode('=', $line, 2);
                        $envVars[trim($key)] = trim($value);
                    }
                }
                break;
            }
        }

        // Set Banco do Brasil API configuration for testing
        $app['config']->set('banco-do-brasil', [
            'base_url' => $envVars['BB_API_BASE_URL'] ?? 'https://api.hm.bb.com.br',
            'oauth_url' => $envVars['BB_OAUTH_URL'] ?? 'https://oauth.hm.bb.com.br',
            'client_id' => $envVars['BB_CLIENT_ID'] ?? 'test-client-id',
            'client_secret' => $envVars['BB_CLIENT_SECRET'] ?? 'test-client-secret',
            'developer_application_key' => $envVars['BB_DEVELOPER_APPLICATION_KEY'] ?? 'test-developer-key',
            'cobranca' => [
                'gw_app_key' => $envVars['BB_GW_APP_KEY'] ?? 'test-gw-app-key',
            ],
            'timeout' => intval($envVars['BB_API_TIMEOUT'] ?? 30),
            'connect_timeout' => intval($envVars['BB_API_CONNECT_TIMEOUT'] ?? 10),
            'convenio' => $envVars['BB_CONVENIO'] ?? '3128557',
            'agencia' => $envVars['BB_AGENCIA'] ?? '1234',
            'conta' => $envVars['BB_CONTA'] ?? '123456',
        ]);
    }
} 