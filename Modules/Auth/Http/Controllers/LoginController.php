<?php

namespace Modules\Auth\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Modules\Auth\Actions\CreateToken;
use Modules\Auth\Http\Requests\LoginRequest;

class LoginController
{
    public function __construct(
        private readonly CreateToken $createToken,
    ) {}

    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->input('email'))->first();

        if (! $user || ! Hash::check($request->input('password'), $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        if ($user->email_verified_at === null) {
            return response()->json([
                'status' => false,
                'message' => __('Please verify your email address before logging in.'),
            ], 403);
        }

        $device = $request->userAgent() ?? 'api';
        $tokens = $this->createToken->pair($user, $device);

        return response()->json([
            'status' => true,
            'message' => __('Login successful'),
            'body' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'token' => $tokens,
            ],
        ]);
    }
}
