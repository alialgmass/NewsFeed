<?php

namespace Modules\Search\Services;

use Illuminate\Support\Facades\Cache;
use Modules\Search\Models\SearchTerm;

class AutocompleteService
{
    protected const CACHE_TTL = 3600;

    protected const CACHE_PREFIX = 'autocomplete:';

    protected Trie $trie;

    public function __construct()
    {
        $this->trie = $this->buildTrie();
    }

    public function suggest(string $query, int $limit = 10): array
    {
        $query = trim(mb_strtolower($query));

        if (mb_strlen($query) < 2) {
            return [];
        }

        return Cache::remember(
            self::CACHE_PREFIX.$query.':'.$limit,
            self::CACHE_TTL,
            fn () => $this->searchFromTrie($query, $limit)
        );
    }

    public function recordSearch(string $term): void
    {
        $searchTerm = SearchTerm::firstOrNew(['term' => mb_strtolower(trim($term))]);
        $searchTerm->increment('frequency');
        $searchTerm->save();

        $this->clearCache($term);
    }

    protected function buildTrie(): Trie
    {
        $trie = new Trie;

        SearchTerm::query()
            ->orderByDesc('frequency')
            ->chunk(100, function ($terms) use ($trie) {
                foreach ($terms as $term) {
                    $trie->insert($term->term, $term->frequency);
                }
            });

        return $trie;
    }

    protected function searchFromTrie(string $query, int $limit): array
    {
        $results = $this->trie->search($query);

        return array_slice($results, 0, $limit);
    }

    protected function clearCache(?string $term = null): void
    {
        if ($term) {
            $prefix = substr($term, 0, 2);
            Cache::forget(self::CACHE_PREFIX.$prefix);
        }
    }
}
