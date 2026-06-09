<?php

namespace Modules\Auth\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Auth\Actions\CreateToken;
use Modules\Auth\Http\Requests\RegisterRequest;

class RegisterController
{
    public function __construct(
        private readonly CreateToken $createToken,
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create($request->validated());

        $user->sendEmailVerificationNotification();

        return response()->json([
            'status' => true,
            'message' => __('Registration successful. Please verify your email before logging in.'),
            'body' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
            ],
        ], 201);
    }
}
