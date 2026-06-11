<?php

namespace Modules\Auth\Tests\Feature;

use App\Enums\TokenAbilityEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TokenTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private string $accessToken;

    private string $refreshToken;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->accessToken = $this->user->createToken(
            'test',
            [TokenAbilityEnum::Access->value],
            TokenAbilityEnum::Access->expiration()
        )->plainTextToken;

        $this->refreshToken = $this->user->createToken(
            'test_refresh',
            [TokenAbilityEnum::Refresh->value],
            TokenAbilityEnum::Refresh->expiration()
        )->plainTextToken;
    }

    public function test_refresh_token_returns_new_access_token(): void
    {
        $response = $this->withToken($this->refreshToken)
            ->postJson('/api/auth/refresh');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'body' => ['access_token', 'token_type', 'expires_in'],
        ]);
    }

    public function test_access_token_cannot_refresh(): void
    {
        $response = $this->withToken($this->accessToken)
            ->postJson('/api/auth/refresh');

        $response->assertStatus(403);
    }

    public function test_logout_revokes_current_token(): void
    {
        $response = $this->withToken($this->accessToken)
            ->postJson('/api/auth/logout');

        $response->assertStatus(200);
        $this->assertCount(1, $this->user->tokens()->get());
    }

    public function test_revoke_all_removes_all_tokens(): void
    {
        $response = $this->withToken($this->accessToken)
            ->postJson('/api/auth/logout/all');

        $response->assertStatus(200);
        $this->assertCount(0, $this->user->tokens()->get());
    }
}
