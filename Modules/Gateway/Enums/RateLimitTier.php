<?php

namespace Modules\Gateway\Enums;

enum RateLimitTier: string
{
    case Basic = 'basic';
    case Pro = 'pro';
    case Enterprise = 'enterprise';
    case Unlimited = 'unlimited';

    public function maxAttempts(): int
    {
        return match ($this) {
            self::Basic => 60,
            self::Pro => 300,
            self::Enterprise => 1000,
            self::Unlimited => 0,
        };
    }

    public function decayMinutes(): int
    {
        return 1;
    }
}
