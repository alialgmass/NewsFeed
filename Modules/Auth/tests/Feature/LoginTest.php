<?php

namespace Modules\Auth\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'email_verified_at' => now(),
        ]);
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => true]);
        $response->assertJsonStructure([
            'body' => [
                'token' => [
                    'access_token',
                    'refresh_token',
                    'token_type',
                    'expires_in',
                ],
            ],
        ]);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422);
    }

    public function test_login_fails_when_email_not_verified(): void
    {
        $this->user->forceFill(['email_verified_at' => null])->save();
        $this->user->refresh();

        $this->assertNull($this->user->email_verified_at);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(403);
        $response->assertJson(['status' => false]);
    }

    public function test_login_returns_bearer_tokens(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $tokens = $response->json('body.token');
        $this->assertNotNull($tokens['access_token']);
        $this->assertNotNull($tokens['refresh_token']);
        $this->assertEquals('Bearer', $tokens['token_type']);
    }
}
