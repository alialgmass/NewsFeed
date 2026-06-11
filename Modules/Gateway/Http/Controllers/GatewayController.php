<?php

namespace Modules\Gateway\Http\Controllers;

use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\JsonResponse;
use Modules\Gateway\Models\ApiKey;

class GatewayController extends ApiController
{
    public static ?string $model = ApiKey::class;

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $keys = ApiKey::filter(request()->all())->paginate();

        return $this->apiBody([
            'api_keys' => $keys,
        ])->apiResponse();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(): JsonResponse
    {
        $validated = request()->validate([
            'name' => ['required', 'string', 'max:255'],
            'rate_limit_tier' => ['nullable', 'string', 'in:basic,pro,enterprise,unlimited'],
        ]);

        $key = ApiKey::generate($validated['name'], $validated);

        return $this->apiMessage(__('API key created successfully'))
            ->apiBody(['api_key' => $key])
            ->apiResponse();
    }

    /**
     * Show the specified resource.
     */
    public function show(ApiKey $key): JsonResponse
    {
        return $this->apiBody(['api_key' => $key])->apiResponse();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ApiKey $key): JsonResponse
    {
        $validated = request()->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $key->update($validated);

        return $this->apiMessage(__('API key updated successfully'))
            ->apiBody(['api_key' => $key->fresh()])
            ->apiResponse();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ApiKey $key): JsonResponse
    {
        $key->delete();

        return $this->apiMessage(__('API key deleted successfully'))->apiResponse();
    }
}
