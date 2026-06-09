<?php

use App\Exceptions\ApiHandler;
use App\Http\Middleware\AppLanguage;
use App\Http\Middleware\CustomThrottleRequests;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use Laravel\Sanctum\Http\Middleware\CheckForAnyAbility;
use Modules\Gateway\Http\Middleware\AuthenticateApiKey;
use Modules\Gateway\Http\Middleware\ValidateWebhookSignature;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->use([
            AppLanguage::class,
        ]);

        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'gateway.api-key' => AuthenticateApiKey::class,
            'gateway.webhook-signature' => ValidateWebhookSignature::class,
            'gateway.throttle' => CustomThrottleRequests::class,
            'ability' => CheckAbilities::class,
            'abilities' => CheckForAnyAbility::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $apiHandler = new ApiHandler;

        $exceptions->render(function (Throwable $e, Request $request) use ($apiHandler) {
            return $apiHandler->handle($e, $request);
        });
    })->create();
