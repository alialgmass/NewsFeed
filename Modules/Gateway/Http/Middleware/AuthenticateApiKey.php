<?php

namespace Modules\Gateway\Http\Middleware;

use App\Exceptions\ApiException\ExceptionResponse;
use Closure;
use Illuminate\Http\Request;
use Modules\Gateway\Contracts\ApiKeyAuthenticatorInterface;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiKey
{
    public function __construct(
        private readonly ApiKeyAuthenticatorInterface $apiKeyService
    ) {}

    /**
     * @throws ExceptionResponse
     */
    public function handle(Request $request, Closure $next): Response
    {
        $header = $request->header('X-Authorization');

        if (! $header) {
            throw ExceptionResponse::instance(__('Missing API key'), 401);
        }

        $apiKey = $this->apiKeyService->validate($header);

        if (! $apiKey) {
            throw ExceptionResponse::instance(__('Invalid or inactive API key'), 401);
        }

        $request->attributes->set('gateway_client_id', $apiKey->getKey());
        $request->attributes->set('gateway_client_tier', $apiKey->rate_limit_tier);

        return $next($request);
    }
}
