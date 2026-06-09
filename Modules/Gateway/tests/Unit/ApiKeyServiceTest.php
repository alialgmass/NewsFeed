<?php

namespace Modules\Gateway\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Modules\Gateway\Services\ApiKeyService;
use Tests\TestCase;

class ApiKeyServiceTest extends TestCase
{
    use RefreshDatabase;

    private ApiKeyService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new ApiKeyService;
    }

    public function test_database_has_api_keys_table(): void
    {
        $this->assertTrue(
            Schema::hasTable('api_keys'),
            'api_keys table does not exist in test database'
        );
    }

    public function test_generates_api_key(): void
    {
        $apiKey = $this->service->generateKey('Test Service Key', [
            'rate_limit_tier' => 'pro',
        ]);

        $this->assertNotNull($apiKey->id);
        $this->assertEquals('Test Service Key', $apiKey->name);
        $this->assertEquals('pro', $apiKey->rate_limit_tier);
        $this->assertTrue($apiKey->is_active);
        $this->assertNotNull($apiKey->key);
        $this->assertEquals(64, strlen($apiKey->key));
    }

    public function test_finds_existing_key(): void
    {
        $created = $this->service->generateKey('Find Test');
        $found = $this->service->find($created->key);

        $this->assertNotNull($found);
        $this->assertEquals($created->id, $found->id);
    }

    public function test_returns_null_for_nonexistent_key(): void
    {
        $result = $this->service->find('nonexistent-key');

        $this->assertNull($result);
    }

    public function test_validates_active_key(): void
    {
        $apiKey = $this->service->generateKey('Validation Test');

        $found = $this->service->find($apiKey->key);
        $this->assertNotNull($found, 'find() returned null');
        $this->assertTrue($found->isValid(), 'isValid() returned false');

        $result = $this->service->validate($apiKey->key);

        $this->assertNotNull($result, 'validate() returned null');
        $this->assertNotNull($result->last_used_at, 'last_used_at is null');
    }

    public function test_validates_inactive_key_as_null(): void
    {
        $apiKey = $this->service->generateKey('Inactive Test', ['is_active' => false]);
        $result = $this->service->validate($apiKey->key);

        $this->assertNull($result);
    }

    public function test_validates_expired_key_as_null(): void
    {
        $apiKey = $this->service->generateKey('Expired Test', [
            'expires_at' => now()->subDay(),
        ]);
        $result = $this->service->validate($apiKey->key);

        $this->assertNull($result);
    }
}
