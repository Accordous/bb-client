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
    private $http;

    /**
     * @var BoletoEndpoint
     */
    private $boletos;
    /**
     * @var ConvenioEndpoint
     */
    private $convenios;
    /**
     * @var WebhookEndpoint
     */
    private $webhooks;

    /**
     * @var array
     */
    private $config;

    /**
     * BancoDoBrasilService constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
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
        $this->boletos = new BoletoEndpoint($this->http);
        $this->convenios = new ConvenioEndpoint($this->http);
        $this->webhooks = new WebhookEndpoint($this->http);
    }

    /**
     * Get OAuth token for API authentication
     *
     * @return string
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

    /**
     * @return BoletoEndpoint
     */
    public function boletos(): BoletoEndpoint
    {
        return $this->boletos;
    }

    /**
     * @return ConvenioEndpoint
     */
    public function convenios(): ConvenioEndpoint
    {
        return $this->convenios;
    }

    /**
     * @return WebhookEndpoint
     */
    public function webhooks(): WebhookEndpoint
    {
        return $this->webhooks;
    }
}