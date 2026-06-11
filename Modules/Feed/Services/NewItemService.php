<?php

namespace Modules\Feed\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Modules\Feed\Events\NewItemReaded;
use Modules\Feed\Models\NewItem;
use Modules\User\Models\InterestCategory;

class NewItemService
{
    private const USER_INTERESTS_CACHE_TTL = 300;

    public function getPaginatedFeed(
        User $user,
        int $perPage = 15
    ): CursorPaginator {
        $categoryIds = $this->getInterestCategories($user);

        return empty($categoryIds)
            ? $this->defaultFeed($perPage)
            : $this->personalizedFeed($categoryIds, $perPage);
    }

    public function getNewItemDetail(
        NewItem $newItem,
        User $user
    ): array {
        event(new NewItemReaded(
            $newItem->new_category_id,
            $user
        ));

        return [
            'news' => $newItem,
            'user_interests' => $user->interestCategories,
        ];
    }

    private function personalizedFeed(
        array $categoryIds,
        int $perPage
    ): CursorPaginator {
        return $this->feedQuery($categoryIds)
            ->cursorPaginate($perPage);
    }

    private function defaultFeed(
        int $perPage
    ): CursorPaginator {
        return NewItem::query()
            ->latest('published_at')
            ->latest('id')
            ->cursorPaginate($perPage);
    }

    private function feedQuery(
        array $categoryIds
    ): Builder {
        return NewItem::query()
            ->whereIn('new_category_id', $categoryIds)
            ->orderByRaw(
                $this->buildCategoryPriorityExpression($categoryIds)
            )
            ->orderByDesc('published_at')
            ->orderByDesc('id');
    }

    private function buildCategoryPriorityExpression(
        array $categoryIds
    ): string {
        $cases = [];

        foreach ($categoryIds as $priority => $categoryId) {
            $cases[] = sprintf(
                'WHEN %d THEN %d',
                $categoryId,
                $priority
            );
        }

        return sprintf(
            'CASE new_category_id %s ELSE 999999 END',
            implode(' ', $cases)
        );
    }

    private function getInterestCategories(
        User $user
    ): array {
        return Cache::remember(
            "user_interests:{$user->id}",
            self::USER_INTERESTS_CACHE_TTL,
            fn () => InterestCategory::query()
                ->where('user_id', $user->id)
                ->orderByDesc('level')
                ->pluck('new_category_id')
                ->all()
        );
    }
}
