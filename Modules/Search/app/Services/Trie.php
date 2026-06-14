<?php

namespace Modules\Search\Services;

class Trie
{
    public function __construct(
        public TrieNode $root = new TrieNode,
    ) {}

    public function insert(string $word, int $weight = 0): void
    {
        $node = $this->root;

        foreach (mb_str_split(mb_strtolower($word)) as $char) {
            if (! isset($node->children[$char])) {
                $node->children[$char] = new TrieNode;
            }

            $node = $node->children[$char];
        }

        $node->isEnd = true;
        $node->value = $word;
        $node->weight = $weight;
    }

    public function search(string $prefix): array
    {
        $node = $this->root;

        foreach (mb_str_split(mb_strtolower($prefix)) as $char) {
            if (! isset($node->children[$char])) {
                return [];
            }

            $node = $node->children[$char];
        }

        $results = [];
        $this->collect($node, $results);

        usort($results, fn (array $a, array $b) => $b['weight'] - $a['weight']);

        return $results;
    }

    protected function collect(TrieNode $node, array &$results): void
    {
        if ($node->isEnd) {
            $results[] = [
                'value' => $node->value,
                'weight' => $node->weight,
            ];
        }

        foreach ($node->children as $child) {
            $this->collect($child, $results);
        }
    }
}
