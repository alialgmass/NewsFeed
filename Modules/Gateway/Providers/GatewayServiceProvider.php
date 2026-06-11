<?php

namespace Modules\Gateway\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Modules\Gateway\Contracts\ApiKeyAuthenticatorInterface;
use Modules\Gateway\Services\ApiKeyService;
use Nwidart\Modules\Support\ModuleServiceProvider;

class GatewayServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'Gateway';

    protected string $nameLower = 'gateway';

    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    public function register(): void
    {
        $this->mergeConfigFrom(
            module_path($this->name, 'config/config.php'),
            'gateway'
        );

        $this->app->bind(
            ApiKeyAuthenticatorInterface::class,
            ApiKeyService::class
        );

        parent::register();
    }

    public function boot(): void
    {
        parent::boot();

        $this->bootRateLimiters();
    }

    private function bootRateLimiters(): void
    {
        RateLimiter::for('gateway:api', function (Request $request) {
            $tier = $request->attributes->get('gateway_client_tier', 'basic');
            $limits = config("gateway.rate_limiting.tiers.{$tier}", '60,1');
            [$maxAttempts, $decayMinutes] = explode(',', $limits);

            if ((int) $maxAttempts === 0) {
                return Limit::none();
            }

            $key = $request->attributes->get('gateway_client_id')
                ?? $request->ip();

            return Limit::perMinute((int) $maxAttempts)->by($key);
        });

        RateLimiter::for('gateway:webhook', function (Request $request) {
            $limit = config('gateway.rate_limiting.webhook', '30,1');
            [$maxAttempts, $decayMinutes] = explode(',', $limit);

            $key = $request->attributes->get('gateway_client_id')
                ?? $request->ip();

            return Limit::perMinute((int) $maxAttempts)->by($key);
        });
    }
}
