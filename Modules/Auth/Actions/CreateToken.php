<?php

namespace Modules\Auth\Actions;

use App\Enums\TokenAbilityEnum;
use App\Models\User;

class CreateToken
{
    public function access(User $user, string $deviceName = 'api'): string
    {
        return $user->createToken(
            $deviceName,
            [TokenAbilityEnum::Access->value],
            TokenAbilityEnum::Access->expiration()
        )->plainTextToken;
    }

    public function refresh(User $user, string $deviceName = 'api'): string
    {
        return $user->createToken(
            $deviceName . '_refresh',
            [TokenAbilityEnum::Refresh->value],
            TokenAbilityEnum::Refresh->expiration()
        )->plainTextToken;
    }

    public function pair(User $user, string $deviceName = 'api'): array
    {
        return [
            'access_token' => $this->access($user, $deviceName),
            'refresh_token' => $this->refresh($user, $deviceName),
            'token_type' => 'Bearer',
            'expires_in' => config('gateway.token.access_lifetime', 15) * 60,
        ];
    }
}
