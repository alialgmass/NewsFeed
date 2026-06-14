<?php

namespace Modules\Search\Services;

use Modules\Search\Models\SearchTerm;

class SearchTracker
{
    public function record(string $term): SearchTerm
    {
        $term = mb_strtolower(trim($term));

        $searchTerm = SearchTerm::firstOrNew(['term' => $term]);
        $searchTerm->increment('frequency');
        $searchTerm->save();

        return $searchTerm;
    }

    public function trending(int $limit = 10): array
    {
        return SearchTerm::query()
            ->orderByDesc('frequency')
            ->limit($limit)
            ->pluck('term')
            ->toArray();
    }
}
