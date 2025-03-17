<?php

namespace BancoDoBrasil\Http;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Client\RequestException;

class BancoDoBrasilClient
{
    /**
     * @var array
     */
    protected $config;

    /**
     * BancoDoBrasilClient constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Get OAuth token for API authentication.
     *
     * @return string
     * @throws RequestException
     */
    public function getToken()
    {
        // Check if token is cached
        $cacheKey = 'bb_api_token';
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $auth = base64_encode($this->config['client_id'] . ':' . $this->config['client_secret']);

        // Usar application/x-www-form-urlencoded conforme esperado pelo servidor OAuth
        $response = Http::asForm()
            ->withHeaders([
                'Authorization' => 'Basic ' . $auth,
            ])
            ->timeout($this->config['timeout'])
            ->post($this->config['oauth_url'] . '/oauth/token', [
                'grant_type' => 'client_credentials',
                'scope' => 'cobrancas.boletos-info cobrancas.boletos-requisicao'
            ]);

        $response->throw();
        $data = $response->json();
        $token = $data['access_token'];
        $expiresIn = $data['expires_in'] - 60; // Subtract 60 seconds to be safe

        // Cache the token
        Cache::put($cacheKey, $token, $expiresIn);

        return $token;
    }

    /**
     * Register a new "Boleto de Cobrança".
     *
     * @param array $data Dados do boleto
     * @return array
     * @throws RequestException
     */
    public function registrarBoletoCobranca(array $data)
    {
        $token = $this->getToken();
        
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        
        // Add developer application key if provided
        if (!empty($this->config['developer_application_key'])) {
            $headers['X-Developer-Application-Key'] = $this->config['developer_application_key'];
        }
        
        // Add gw-app-key if provided
        if (!empty($this->config['cobranca']['gw_app_key'])) {
            $headers['X-Application-Key'] = $this->config['cobranca']['gw_app_key'];
        }
        
        $response = Http::withHeaders($headers)
            ->timeout($this->config['timeout'])
            ->post($this->config['base_url'] . '/cobrancas/v2/boletos', $data);
        
        $response->throw();
        
        return $response->json();
    }
} 