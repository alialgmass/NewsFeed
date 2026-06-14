<?php

namespace Modules\Gateway\Http\Controllers;

use App\Enums\HttpCodeEnum;
use App\Http\Controllers\BaseController;
use App\Support\Hook\HookResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController extends BaseController
{
    use HookResponse;

    public function handle(Request $request, string $provider): JsonResponse
    {
        $event = $request->header(config('gateway.webhook.event_header', 'X-Webhook-Event'), 'unknown');

        $payload = [
            'provider' => $provider,
            'event' => $event,
            'received_at' => now()->toIso8601String(),
        ];

        return $this->apiBody($payload)
            ->apiMessage(__('Webhook received'))
            ->apiCode(HttpCodeEnum::ACCEPTED->value)
            ->apiCustomCode(2020)
            ->apiResponse();
    }
}
