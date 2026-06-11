<?php

namespace Modules\Gateway\Tests\Unit;

use Modules\Gateway\Enums\RateLimitTier;
use Tests\TestCase;

class RateLimitTierTest extends TestCase
{
    public function test_basic_tier_limits(): void
    {
        $tier = RateLimitTier::Basic;

        $this->assertEquals('basic', $tier->value);
        $this->assertEquals(60, $tier->maxAttempts());
        $this->assertEquals(1, $tier->decayMinutes());
    }

    public function test_pro_tier_limits(): void
    {
        $tier = RateLimitTier::Pro;

        $this->assertEquals(300, $tier->maxAttempts());
    }

    public function test_enterprise_tier_limits(): void
    {
        $tier = RateLimitTier::Enterprise;

        $this->assertEquals(1000, $tier->maxAttempts());
    }

    public function test_unlimited_tier_limits(): void
    {
        $tier = RateLimitTier::Unlimited;

        $this->assertEquals(0, $tier->maxAttempts());
    }
}
