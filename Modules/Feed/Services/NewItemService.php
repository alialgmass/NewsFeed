<?php

namespace Modules\Feed\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Modules\Feed\Events\NewItemReaded;
use Modules\Feed\Models\NewItem;
use Modules\User\Models\InterestCategory;

class NewItemService
{
    private const CACHE_TTL = 300;

    private const FIRST_PAGE_CACHE_TTL = 60;

    public function getPaginatedFeed(User $user, int $perPage = 15): CursorPaginator
    {
        return NewItem::query()
            ->leftJoin('interst_categories as ic', function ($join) use ($user) {
                $join->on('new_items.new_category_id', '=', 'ic.new_category_id')
                    ->where('ic.user_id', $user->id);
            })
            ->select('new_items.*')
            ->orderByDesc('ic.level')
            ->orderByDesc('new_items.published_at')
            ->cursorPaginate($perPage);
    }

    public function getNewItemDetail(NewItem $newItem, User $user): array
    {
        event(new NewItemReaded($newItem->new_category_id, $user));

        return [
            'news' => $newItem,
            'user_intersts' => $user->interestCategories,
        ];
    }

    private function getInterestCategories(User $user): Collection
    {
        return Cache::remember(
            'user_interests:'.$user->id,
            self::CACHE_TTL,
            fn () => InterestCategory::where('user_id', $user->id)
                ->orderByDesc('level')
                ->get(['new_category_id', 'level'])
        );
    }
}
