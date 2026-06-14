<?php

namespace Modules\Gateway\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Modules\Gateway\Models\ApiKey;
use Tests\TestCase;

class RateLimitingTest extends TestCase
{
    use RefreshDatabase;

    private ApiKey $apiKey;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiKey = ApiKey::generate('Rate Limit Test', [
            'rate_limit_tier' => 'basic',
        ]);
    }

    public function test_rate_limited_after_exceeding_limit(): void
    {
        config(['gateway.rate_limiting.tiers.basic' => '2,1']);

        Route::get('/api/_rate-test', function () {
            return response()->json(['status' => true]);
        })->middleware(['gateway.api-key', 'throttle:gateway:api']);

        $this->withHeader('X-Authorization', $this->apiKey->key)
            ->getJson('/api/_rate-test')
            ->assertStatus(200);

        $this->withHeader('X-Authorization', $this->apiKey->key)
            ->getJson('/api/_rate-test')
            ->assertStatus(200);

        $this->withHeader('X-Authorization', $this->apiKey->key)
            ->getJson('/api/_rate-test')
            ->assertStatus(429);
    }

    public function test_unlimited_tier_not_rate_limited(): void
    {
        config(['gateway.rate_limiting.tiers.unlimited' => '0,1']);
        $this->apiKey->update(['rate_limit_tier' => 'unlimited']);

        Route::get('/api/_rate-unlimited', function () {
            return response()->json(['status' => true]);
        })->middleware(['gateway.api-key', 'throttle:gateway:api']);

        foreach (range(1, 10) as $i) {
            $this->withHeader('X-Authorization', $this->apiKey->key)
                ->getJson('/api/_rate-unlimited')
                ->assertStatus(200);
        }
    }
}
