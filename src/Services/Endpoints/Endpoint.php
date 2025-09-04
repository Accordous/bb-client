<?php

namespace Accordous\BbClient\Services\Endpoints;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Validator;

abstract class Endpoint
{
    protected PendingRequest $http;
    protected string $developerApplicationKey;
    protected string $convenio;

    public function __construct(PendingRequest $http, string $developerApplicationKey, string $convenio = '')
    {
        $this->http = $http;
        $this->developerApplicationKey = $developerApplicationKey;
        $this->convenio = $convenio;
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
        $queryParams['gw-dev-app-key'] = $this->developerApplicationKey;
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
            $baseUrl = config('banco-do-brasil.base_url');
            $url = $baseUrl . $url;
        }
        
        // Get token from the current HTTP client headers
        $headers = $this->client()->getOptions()['headers'] ?? [];
        $authHeader = $headers['Authorization'] ?? '';
        
        if (empty($authHeader)) {
            // Fallback: This should not happen in normal operation
            // as credentials are now passed per instance
            throw new \Exception('Authentication header not found and credentials not available for fallback');
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