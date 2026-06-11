<?php

namespace Modules\Gateway\Http\Middleware;

use App\Exceptions\ApiException\ExceptionResponse;
use Closure;
use Illuminate\Http\Request;
use Modules\Gateway\Models\ApiKey;
use Symfony\Component\HttpFoundation\Response;

class ValidateWebhookSignature
{
    /**
     * @throws ExceptionResponse
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('options')) {
            return $next($request);
        }

        $signature = $request->header(config('gateway.webhook.signature_header', 'X-Webhook-Signature'));

        if (! $signature) {
            throw ExceptionResponse::instance(__('Missing webhook signature'), 401);
        }

        $parts = explode(',', $signature);
        $timestamp = null;
        $signatures = [];

        foreach ($parts as $part) {
            if (str_starts_with($part, 't=')) {
                $timestamp = (int) substr($part, 2);
            } elseif (str_starts_with($part, 'v1=')) {
                $signatures[] = substr($part, 3);
            }
        }

        if (! $timestamp || empty($signatures)) {
            throw ExceptionResponse::instance(__('Invalid webhook signature format'), 401);
        }

        $tolerance = config('gateway.webhook.tolerance', 300);

        if (abs(now()->timestamp - $timestamp) > $tolerance) {
            throw ExceptionResponse::instance(__('Webhook signature expired'), 401);
        }

        $clientId = $request->attributes->get('gateway_client_id');
        $secret = $this->resolveSecret($clientId);

        $payload = $request->getContent();
        $isValid = false;

        foreach ($signatures as $sig) {
            if (hash_equals(hash_hmac('sha256', $timestamp.'.'.$payload, $secret), $sig)) {
                $isValid = true;
                break;
            }
        }

        if (! $isValid) {
            throw ExceptionResponse::instance(__('Invalid webhook signature'), 401);
        }

        $request->attributes->set('gateway_webhook_timestamp', $timestamp);

        return $next($request);
    }

    protected function resolveSecret(?int $clientId): string
    {
        if ($clientId) {
            $apiKey = ApiKey::find($clientId);

            if ($apiKey && isset($apiKey->metadata['webhook_secret'])) {
                return $apiKey->metadata['webhook_secret'];
            }
        }

        return config('gateway.webhook.secret', env('WEBHOOK_SECRET', ''));
    }
}
