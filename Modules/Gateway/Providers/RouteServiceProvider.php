<?php

namespace Modules\Gateway\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    protected string $name = 'Gateway';

    public function boot(): void
    {
        parent::boot();
    }

    public function map(): void
    {
        $this->mapApiRoutes();
        $this->mapWebhookRoutes();
        $this->mapWebRoutes();
    }

    protected function mapWebRoutes(): void
    {
        Route::middleware('web')
            ->group(module_path($this->name, '/routes/web.php'));
    }

    protected function mapApiRoutes(): void
    {
        Route::prefix('api')
            ->middleware(['api', 'gateway.api-key', 'throttle:gateway:api'])
            ->name('api.')
            ->group(module_path($this->name, '/routes/api.php'));
    }

    protected function mapWebhookRoutes(): void
    {
        Route::prefix('api/webhooks')
            ->middleware(['api', 'gateway.api-key', 'gateway.webhook-signature', 'throttle:gateway:webhook'])
            ->name('api.webhooks.')
            ->group(module_path($this->name, '/routes/webhooks.php'));
    }
}
