<?php

namespace Modules\Auth\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Modules\Auth\Actions\CreateToken;
use Modules\Auth\Http\Requests\RegisterRequest;

class RegisterController extends ApiController
{
    public function __construct(
        private readonly CreateToken $createToken,
    ) {
        parent::__construct();
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create($request->validated());

        $user->sendEmailVerificationNotification();

        return $this->apiBody([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ])->apiResponse();
    }
}
