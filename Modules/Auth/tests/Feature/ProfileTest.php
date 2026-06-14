<?php

namespace Modules\Auth\Tests\Feature;

use App\Enums\TokenAbilityEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private string $accessToken;

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
    }

    public function test_authenticated_user_can_access_profile(): void
    {
        $response = $this->withToken($this->accessToken)
            ->getJson('/api/auth/me');

        $response->assertStatus(200);
        $response->assertJson(['status' => true]);
        $response->assertJsonPath('body.user.email', $this->user->email);
    }

    public function test_unauthenticated_user_cannot_access_profile(): void
    {
        $response = $this->getJson('/api/auth/me');

        $response->assertStatus(401);
    }

    public function test_expired_token_is_rejected(): void
    {
        $expiredToken = $this->user->createToken(
            'expired',
            [TokenAbilityEnum::Access->value],
            now()->subMinute()
        )->plainTextToken;

        $response = $this->withToken($expiredToken)
            ->getJson('/api/auth/me');

        $response->assertStatus(401);
    }
}
