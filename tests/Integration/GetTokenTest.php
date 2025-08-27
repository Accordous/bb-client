<?php

namespace Tests\Integration;

use Tests\TestCase;
use Accordous\BbClient\Facades\BancoDoBrasil;
use Illuminate\Support\Facades\Cache;

class GetTokenTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Clear the cache before each test
        Cache::flush();
        
        // Skip this test if environment variables are not set
        if (empty(config('banco-do-brasil.client_id')) || 
            empty(config('banco-do-brasil.client_secret')) ||
            config('banco-do-brasil.client_id') === 'test-client-id') {
            $this->markTestSkipped('API credentials not configured for real API testing.');
        }
    }

    /** @test */
    public function it_can_get_oauth_token_from_real_api()
    {
        // Clear any cached tokens to force a real API call
        Cache::forget('bb_api_token');
        
        // Get a real token from the API
        $token = BancoDoBrasil::getToken();

        // Verify the token is not empty and has expected characteristics
        $this->assertNotEmpty($token);
        $this->assertIsString($token);
        
        // Real tokens are usually longer than simple test tokens
        $this->assertGreaterThan(10, strlen($token));
        
        // BB tokens typically contain alphanumeric characters, hyphens, underscores and dots
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9\-_\.]+$/', $token);
    }

    /** @test */
    public function it_caches_token_after_first_request()
    {
        // Clear any existing cached tokens
        Cache::forget('bb_api_token');
        
        // First call should hit the API
        $firstToken = BancoDoBrasil::getToken();
        
        // Verify token is cached
        $cachedToken = Cache::get('bb_api_token');
        $this->assertEquals($firstToken, $cachedToken);
        
        // Second call should return the same cached token
        $secondToken = BancoDoBrasil::getToken();
        $this->assertEquals($firstToken, $secondToken);
    }

    /** @test */
    public function it_handles_token_expiration_gracefully()
    {
        // Clear any existing cached tokens
        Cache::forget('bb_api_token');
        
        // Get a fresh token
        $token = BancoDoBrasil::getToken();
        $this->assertNotEmpty($token);
        
        // Manually expire the cached token by setting a very short TTL
        Cache::put('bb_api_token', $token, 1); // 1 second
        
        // Wait for token to expire
        sleep(2);
        
        // Next call should get a new token
        $newToken = BancoDoBrasil::getToken();
        $this->assertNotEmpty($newToken);
        $this->assertIsString($newToken);
        
        // Note: We can't easily verify that it's actually a different token
        // because the BB API might return the same token if it's still valid
    }

    /** @test */
    public function it_returns_cached_token_if_available()
    {
        // Put a test token in the cache with a long TTL
        $cachedToken = 'cached-token-' . time();
        Cache::put('bb_api_token', $cachedToken, 3600);

        // Call the method - should return cached token instead of making API call
        $token = BancoDoBrasil::getToken();

        // Assert we got the cached token
        $this->assertEquals($cachedToken, $token);
    }

    /** @test */
    public function it_validates_token_format()
    {
        $token = BancoDoBrasil::getToken();
        
        // Basic validation of token format
        $this->assertNotEmpty($token);
        $this->assertIsString($token);
        
        // BB tokens should not contain spaces
        $this->assertStringNotContainsString(' ', $token);
        
        // Should not contain special characters that would indicate an error message
        $this->assertStringNotContainsString('error', strtolower($token));
        $this->assertStringNotContainsString('invalid', strtolower($token));
        $this->assertStringNotContainsString('expired', strtolower($token));
    }

    /** @test */
    public function it_handles_network_timeouts_gracefully()
    {
        // This test ensures the client handles timeouts properly
        // We can't easily simulate a timeout in integration tests,
        // but we can verify the token request completes within reasonable time
        
        $startTime = microtime(true);
        
        $token = BancoDoBrasil::getToken();
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        // Token request should complete within configured timeout (default 30 seconds)
        $maxExpectedTime = config('banco-do-brasil.timeout', 30);
        $this->assertLessThan($maxExpectedTime, $executionTime, 
            "Token request took too long: {$executionTime} seconds");
        
        $this->assertNotEmpty($token);
    }
}