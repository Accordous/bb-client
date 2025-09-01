<?php

namespace Accordous\BbClient\Services\Endpoints;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Validator;

abstract class Endpoint
{
    protected PendingRequest $http;

    public function __construct(PendingRequest $http)
    {
        $this->http = $http;
    }

    protected function client(): PendingRequest
    {
        return $this->http;
    }

    /**
     * Add gw-dev-app-key to query parameters for all requests
     */
    protected function addDevAppKey(array $queryParams = []): array
    {
        $queryParams['gw-dev-app-key'] = config('banco-do-brasil.developer_application_key');
        return $queryParams;
    }

    protected function validate(array $attributes): array
    {
        return Validator::validate($attributes, $this->rules(), $this->messages());
    }

    protected function rules(): array
    {
        return [];
    }

    protected function messages(): array
    {
        return [];
    }

    protected function attributes(): array
    {
        return [];
    }

    /**
     * Make a cURL request as fallback when Laravel HTTP client fails to capture error responses
     */
    protected function makeCurlRequest(string $method, string $url, array $body = []): Response
    {
        // Ensure URL is complete (add base URL if needed)
        if (!str_starts_with($url, 'http')) {
            $baseUrl = config('banco-do-brasil.base_url', 'https://api.hm.bb.com.br');
            $url = $baseUrl . $url;
        }
        
        // Get token from the current HTTP client headers
        $headers = $this->client()->getOptions()['headers'] ?? [];
        $authHeader = $headers['Authorization'] ?? '';
        
        if (empty($authHeader)) {
            // Fallback: get token using OAuth
            $client_id = config('banco-do-brasil.client_id');
            $client_secret = config('banco-do-brasil.client_secret');
            $oauth_url = config('banco-do-brasil.oauth_url', 'https://oauth.hm.bb.com.br/oauth/token');
            
            $auth = base64_encode($client_id . ':' . $client_secret);
            $tokenResponse = \Illuminate\Support\Facades\Http::asForm()
                ->withHeaders(['Authorization' => 'Basic ' . $auth])
                ->timeout(30)
                ->post($oauth_url, [
                    'grant_type' => 'client_credentials',
                    'scope' => 'cobrancas.boletos-info cobrancas.boletos-requisicao cobrancas.convenio-requisicao'
                ]);
            
            if ($tokenResponse->successful()) {
                $tokenData = $tokenResponse->json();
                $authHeader = 'Bearer ' . $tokenData['access_token'];
            }
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: ' . $authHeader,
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        // Add body for POST/PATCH/PUT requests
        if (!empty($body) && in_array($method, ['POST', 'PATCH', 'PUT'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        
        $header = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);
        
        curl_close($ch);
        
        // Parse headers manually
        $headerLines = explode("\r\n", trim($header));
        $parsedHeaders = [];
        
        foreach ($headerLines as $line) {
            if (strpos($line, ':') !== false) {
                list($key, $value) = explode(':', $line, 2);
                $parsedHeaders[trim($key)] = [trim($value)];
            }
        }
        
        // Create a Laravel-like Response object using Http facade
        return new Response(
            new \GuzzleHttp\Psr7\Response($httpCode, $parsedHeaders, $body)
        );
    }
}