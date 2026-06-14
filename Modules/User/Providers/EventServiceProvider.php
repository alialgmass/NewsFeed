<?php

namespace Modules\User\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Feed\Events\NewsItemRead;
use Modules\User\Listeners\AddInterestOnNewsItem;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [
        NewsItemRead::class => [
            AddInterestOnNewsItem::class,
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
