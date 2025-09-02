<?php

namespace Accordous\BbClient\Services;

use Accordous\BbClient\Services\Endpoints\BoletoEndpoint;
use Accordous\BbClient\Services\Endpoints\ConvenioEndpoint;
use Accordous\BbClient\Services\Endpoints\WebhookEndpoint;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Client\PendingRequest;

class BancoDoBrasilService
{
    /**
     * @var PendingRequest
     */
    private PendingRequest $http;

    /**
     * @var BoletoEndpoint
     */
    private BoletoEndpoint $boletos;
    /**
     * @var ConvenioEndpoint
     */
    private ConvenioEndpoint $convenios;
    /**
     * @var WebhookEndpoint
     */
    private WebhookEndpoint $webhooks;

    /**
     * @var array
     */
    private array $config;

    /**
     * BancoDoBrasilService constructor.
     */
    public function __construct(string $clientId, string $clientSecret, string $developerApplicationKey, string $convenio = '')
    {
        $this->config = [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'developer_application_key' => $developerApplicationKey,
            'convenio' => $convenio,
            'base_url' => config('banco-do-brasil.base_url'),
            'oauth_url' => config('banco-do-brasil.oauth_url'),
            'timeout' => config('banco-do-brasil.timeout', 30),
            'connect_timeout' => config('banco-do-brasil.connect_timeout', 10),
        ];
        $this->setupHttpClient();
        $this->initializeEndpoints();
    }

    /**
     * Setup HTTP client with authentication
     */
    private function setupHttpClient(): void
    {
        $token = $this->getToken();
        
        $this->http = Http::withOptions(['verify' => false])
            ->baseUrl($this->config['base_url'])
            ->withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])
            ->timeout($this->config['timeout']);
    }

    /**
     * Initialize endpoint services
     */
    private function initializeEndpoints(): void
    {
        $this->boletos = new BoletoEndpoint($this->http, $this->config['developer_application_key'], $this->config['convenio']);
        $this->convenios = new ConvenioEndpoint($this->http, $this->config['developer_application_key'], $this->config['convenio']);
        $this->webhooks = new WebhookEndpoint($this->http, $this->config['developer_application_key'], $this->config['convenio']);
    }

    /**
     * Get OAuth token for API authentication
     */
    public function getToken(): string
    {
        $cacheKey = 'bb_api_token';
        
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $auth = base64_encode($this->config['client_id'] . ':' . $this->config['client_secret']);

        $response = Http::asForm()
            ->withHeaders([
                'Authorization' => 'Basic ' . $auth,
            ])
            ->timeout($this->config['timeout'])
            ->post($this->config['oauth_url'], [
                'grant_type' => 'client_credentials',
                'scope' => 'cobrancas.boletos-info cobrancas.boletos-requisicao cobrancas.convenio-requisicao'
            ]);

        $response->throw();
        $data = $response->json();
        $token = $data['access_token'];
        $expiresIn = $data['expires_in'] - 60; // Subtract 60 seconds to be safe

        Cache::put($cacheKey, $token, $expiresIn);

        return $token;
    }

    public function boletos(): BoletoEndpoint
    {
        return $this->boletos;
    }

    public function convenios(): ConvenioEndpoint
    {
        return $this->convenios;
    }

    public function webhooks(): WebhookEndpoint
    {
        return $this->webhooks;
    }
}