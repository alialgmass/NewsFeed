<?php

namespace Modules\Gateway\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\JsonResponse;
use Modules\Gateway\Http\Requests\StoreApiKeyRequest;
use Modules\Gateway\Http\Requests\UpdateApiKeyRequest;
use Modules\Gateway\Models\ApiKey;

class GatewayController extends ApiController
{
    public static ?string $model = ApiKey::class;

    public function index(): JsonResponse
    {
        $keys = ApiKey::filter(request()->all())->paginate();

        return $this->apiBody([
            'api_keys' => $keys,
        ])->apiResponse();
    }

    public function store(StoreApiKeyRequest $request): JsonResponse
    {
        $key = ApiKey::generate($request->input('name'), $request->validated());

        return $this->apiMessage(__('API key created successfully'))
            ->apiBody(['api_key' => $key])
            ->apiResponse();
    }

    public function show(ApiKey $key): JsonResponse
    {
        return $this->apiBody(['api_key' => $key])->apiResponse();
    }

    public function update(UpdateApiKeyRequest $request, ApiKey $key): JsonResponse
    {
        $key->update($request->validated());

        return $this->apiMessage(__('API key updated successfully'))
            ->apiBody(['api_key' => $key->fresh()])
            ->apiResponse();
    }

    public function destroy(ApiKey $key): JsonResponse
    {
        $key->delete();

        return $this->apiMessage(__('API key deleted successfully'))->apiResponse();
    }
}
