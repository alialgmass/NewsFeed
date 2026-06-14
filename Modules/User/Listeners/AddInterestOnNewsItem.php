<?php

namespace Modules\User\Listeners;

use Modules\Feed\Events\NewsItemRead;
use Modules\User\Models\InterestCategory;

class AddInterestOnNewsItem
{
    /**
     * Create the event listener.
     */
    public function __construct() {}

    /**
     * Handle the event.
     */
    public function handle(NewsItemRead $event): void
    {
        $interest = InterestCategory::query()->firstOrCreate(
            [
                'new_category_id' => $event->new_category_id,
                'user_id' => $event->user?->id,
            ],
            [
                'level' => 0,
            ]
        );

        $interest->increment('level', .00001);
    }
}
