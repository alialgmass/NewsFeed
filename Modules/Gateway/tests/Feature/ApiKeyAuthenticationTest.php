<?php

namespace Modules\Gateway\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Gateway\Models\ApiKey;
use Tests\TestCase;

class ApiKeyAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    private ApiKey $apiKey;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiKey = ApiKey::generate('Test Key', [
            'rate_limit_tier' => 'basic',
        ]);
    }

    public function test_request_without_api_key_returns_401(): void
    {
        $response = $this->getJson('/api/user');

        $response->assertStatus(401);
        $response->assertJson(['status' => false]);
    }

    public function test_request_with_valid_api_key_passes(): void
    {
        $response = $this->withHeader('X-Authorization', $this->apiKey->key)
            ->getJson('/api/user');

        $response->assertStatus(401);
        $response->assertJsonMissing(['message' => __('Invalid or inactive API key')]);
    }

    public function test_request_with_invalid_api_key_returns_401(): void
    {
        $response = $this->withHeader('X-Authorization', 'invalid-key-12345')
            ->getJson('/api/user');

        $response->assertStatus(401);
        $response->assertJson(['status' => false]);
    }

    public function test_request_with_expired_api_key_returns_401(): void
    {
        $this->apiKey->update(['expires_at' => now()->subDay()]);

        $response = $this->withHeader('X-Authorization', $this->apiKey->key)
            ->getJson('/api/user');

        $response->assertStatus(401);
    }

    public function test_request_with_inactive_api_key_returns_401(): void
    {
        $this->apiKey->update(['is_active' => false]);

        $response = $this->withHeader('X-Authorization', $this->apiKey->key)
            ->getJson('/api/user');

        $response->assertStatus(401);
    }
}
