<?php

namespace Modules\Auth\Http\Controllers\Api;

use App\Enums\TokenAbilityEnum;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TokenController extends ApiController
{
    public function refresh(Request $request): JsonResponse
    {
        $user = $request->user();

        $user->currentAccessToken()->delete();

        $token = $user->createToken(
            $request->userAgent() ?? 'api',
            [TokenAbilityEnum::Access->value],
            TokenAbilityEnum::Access->expiration()
        );

        return $this->apiBody([
            'access_token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'expires_in' => config('sanctum.expiration', 15) * 60,
        ])->apiResponse();
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->apiMessage(__('Logged out successfully'))->apiResponse();
    }

    public function revokeAll(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return $this->apiMessage(__('All tokens revoked successfully'))->apiResponse();
    }
}
