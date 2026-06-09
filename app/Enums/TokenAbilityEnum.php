<?php

namespace App\Enums;

use Carbon\Carbon;

enum TokenAbilityEnum: string
{
    case Access = 'access';
    case Refresh = 'refresh';
    case ChangePassword = 'change_password';

    public function middleware(): string
    {
        return 'ability:'.$this->value;
    }

    public function scope(): array
    {
        return [$this->value];
    }

    public function expiration(): Carbon
    {
        return match ($this) {
            self::Access => Carbon::now()->addMinutes(15),
            self::Refresh => Carbon::now()->addDays(7),
            self::ChangePassword => Carbon::now()->addSeconds(520),
        };
    }

    public function exceptionMessage(): string
    {
        return match ($this) {
            self::Access => __('Access token invalid or expired'),
            self::Refresh => __('Refresh token invalid or expired'),
            self::ChangePassword => __('Change password token invalid or expired'),
        };
    }
}
