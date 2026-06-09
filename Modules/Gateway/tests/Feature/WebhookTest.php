<?php

namespace Modules\Gateway\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Gateway\Models\ApiKey;
use Tests\TestCase;

class WebhookTest extends TestCase
{
    use RefreshDatabase;

    private ApiKey $apiKey;

    private string $webhookSecret = 'test-webhook-secret';

    protected function setUp(): void
    {
        parent::setUp();

        config(['gateway.webhook.secret' => $this->webhookSecret]);

        $this->apiKey = ApiKey::generate('Webhook Test', [
            'rate_limit_tier' => 'basic',
            'metadata' => ['webhook_secret' => $this->webhookSecret],
        ]);
    }

    public function test_webhook_with_valid_signature_returns_202(): void
    {
        $payload = ['event' => 'user.created', 'data' => ['id' => 1]];
        $body = json_encode($payload);
        $timestamp = now()->timestamp;
        $signature = 't='.$timestamp.',v1='.hash_hmac('sha256', $timestamp.'.'.$body, $this->webhookSecret);

        $response = $this->withHeaders([
            'X-Authorization' => $this->apiKey->key,
            'X-Webhook-Signature' => $signature,
            'X-Webhook-Event' => 'user.created',
            'Content-Type' => 'application/json',
        ])->postJson('/api/webhooks/github', $payload);

        $response->assertStatus(202);
        $response->assertJson(['status' => true]);
    }

    public function test_webhook_without_signature_returns_401(): void
    {
        $response = $this->withHeaders([
            'X-Authorization' => $this->apiKey->key,
            'X-Webhook-Event' => 'user.created',
            'Content-Type' => 'application/json',
        ])->postJson('/api/webhooks/github', ['event' => 'test']);

        $response->assertStatus(401);
    }

    public function test_webhook_with_invalid_signature_returns_401(): void
    {
        $response = $this->withHeaders([
            'X-Authorization' => $this->apiKey->key,
            'X-Webhook-Signature' => 't='.now()->timestamp.',v1=invalid-signature',
            'X-Webhook-Event' => 'user.created',
            'Content-Type' => 'application/json',
        ])->postJson('/api/webhooks/github', ['event' => 'test']);

        $response->assertStatus(401);
    }

    public function test_webhook_with_expired_signature_returns_401(): void
    {
        $payload = ['event' => 'test'];
        $body = json_encode($payload);
        $timestamp = now()->subMinutes(10)->timestamp;
        $signature = 't='.$timestamp.',v1='.hash_hmac('sha256', $timestamp.'.'.$body, $this->webhookSecret);

        $response = $this->withHeaders([
            'X-Authorization' => $this->apiKey->key,
            'X-Webhook-Signature' => $signature,
            'X-Webhook-Event' => 'test',
            'Content-Type' => 'application/json',
        ])->postJson('/api/webhooks/github', $payload);

        $response->assertStatus(401);
    }
}
