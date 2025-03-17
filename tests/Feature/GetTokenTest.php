<?php

namespace BancoDoBrasil\Tests\Feature;

use BancoDoBrasil\Tests\TestCase;
use BancoDoBrasil\Facades\BancoDoBrasil;
use Illuminate\Support\Facades\Cache;

class GetTokenTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Clear the cache before each test
        Cache::flush();
    }

    /** @test */
    public function it_can_get_oauth_token()
    {
        // Skip this test if environment variables are not set
        if (empty(config('banco-do-brasil.client_id')) || 
            empty(config('banco-do-brasil.client_secret'))) {
            $this->markTestSkipped('API credentials not configured.');
        }
        
        // Clear any cached tokens
        Cache::forget('bb_api_token');
        
        // Get a real token
        $token = BancoDoBrasil::getToken();

        // Verify the token is not empty
        $this->assertNotEmpty($token);
        $this->assertIsString($token);
    }

    /** @test */
    public function it_returns_cached_token_if_available()
    {
        // Put a test token in the cache
        $cachedToken = 'cached-token-' . time();
        Cache::put('bb_api_token', $cachedToken, 3600);

        // Call the method
        $token = BancoDoBrasil::getToken();

        // Assert we got the cached token
        $this->assertEquals($cachedToken, $token);
    }
} 