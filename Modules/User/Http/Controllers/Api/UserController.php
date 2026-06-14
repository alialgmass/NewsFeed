<?php

namespace Modules\User\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Modules\User\Http\Requests\CreateUserRequest;
use Modules\User\Http\Requests\UpdateUserRequest;

class UserController extends ApiController
{
    public static ?string $model = User::class;

    public function index(): JsonResponse
    {
        $users = User::paginate($this->perPage);

        return $this->apiBody([
            'users' => $users,
        ])->apiResponse();
    }

    public function store(CreateUserRequest $request): JsonResponse
    {
        $user = User::create($request->validated());

        return $this->apiBody(['user' => $user])
            ->apiCode(201)
            ->apiResponse();
    }

    public function show(User $user): JsonResponse
    {
        return $this->apiBody(['user' => $user])->apiResponse();
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $user->update($request->validated());

        return $this->apiBody(['user' => $user->fresh()])
            ->apiMessage(__('User updated successfully'))
            ->apiResponse();
    }

    public function destroy(User $user): JsonResponse
    {
        $user->delete();

        return $this->apiMessage(__('User deleted successfully'))->apiResponse();
    }
}
