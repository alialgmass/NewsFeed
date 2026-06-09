<?php

namespace Modules\Auth\Http\Controllers;

use App\Enums\TokenAbilityEnum;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TokenController
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

        return response()->json([
            'status' => true,
            'message' => __('Token refreshed'),
            'body' => [
                'access_token' => $token->plainTextToken,
                'token_type' => 'Bearer',
                'expires_in' => config('sanctum.expiration', 15) * 60,
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => true,
            'message' => __('Logged out successfully'),
        ]);
    }

    public function revokeAll(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'status' => true,
            'message' => __('All tokens revoked successfully'),
        ]);
    }
}
