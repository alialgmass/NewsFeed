<?php

namespace Modules\Auth\Tests\Unit;

use App\Enums\TokenAbilityEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Auth\Actions\CreateToken;
use Tests\TestCase;

class CreateTokenTest extends TestCase
{
    use RefreshDatabase;

    private CreateToken $action;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = new CreateToken;
        $this->user = User::factory()->create();
    }

    public function test_creates_access_token_with_correct_ability(): void
    {
        $token = $this->action->access($this->user);

        $this->assertNotNull($token);
    }

    public function test_creates_refresh_token_with_correct_ability(): void
    {
        $token = $this->action->refresh($this->user);

        $this->assertNotNull($token);
    }

    public function test_pair_returns_both_tokens(): void
    {
        $pair = $this->action->pair($this->user);

        $this->assertArrayHasKey('access_token', $pair);
        $this->assertArrayHasKey('refresh_token', $pair);
        $this->assertArrayHasKey('token_type', $pair);
        $this->assertArrayHasKey('expires_in', $pair);
        $this->assertEquals('Bearer', $pair['token_type']);
    }

    public function test_tokens_have_different_abilities(): void
    {
        $this->action->pair($this->user);

        $tokens = $this->user->tokens()->get();

        $abilities = $tokens->pluck('abilities')->flatten()->all();

        $this->assertContains(TokenAbilityEnum::Access->value, $abilities);
        $this->assertContains(TokenAbilityEnum::Refresh->value, $abilities);
    }
}
