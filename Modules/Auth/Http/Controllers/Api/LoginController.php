<?php

namespace Modules\Auth\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Modules\Auth\Actions\CreateToken;
use Modules\Auth\Http\Requests\LoginRequest;

class LoginController extends ApiController
{
    public function __construct(
        private readonly CreateToken $createToken,
    ) {
        parent::__construct();
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->input('email'))->first();

        if (! $user || ! Hash::check($request->input('password'), $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        if ($user->email_verified_at === null) {
            return $this->apiMessage(__('Please verify your email address before logging in.'))
                ->apiCode(403)
                ->apiResponse();
        }

        $device = $request->userAgent() ?? 'api';
        $tokens = $this->createToken->pair($user, $device);

        return $this->apiBody([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'token' => $tokens,
        ])->apiResponse();
    }
}
