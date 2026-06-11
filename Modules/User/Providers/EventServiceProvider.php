<?php

namespace Modules\User\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Feed\Events\NewItemReaded;
use Modules\User\Listeners\AddInterstOnNewItem;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [
        NewItemReaded::class => [
            AddInterstOnNewItem::class,
        ],
    ];

    /**
     * Indicates if events should be discovered.
     *
     * @var bool
     */
    protected static $shouldDiscoverEvents = true;

    /**
     * Configure the proper event listeners for email verification.
     */
    protected function configureEmailVerification(): void {}

    protected function discoverEventsWithin(): array
    {
        return [
            base_path('Modules/User/Listeners'),
        ];
    }
}
