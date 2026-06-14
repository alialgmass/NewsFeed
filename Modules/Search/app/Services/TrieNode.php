<?php

namespace Modules\Search\Services;

class TrieNode
{
    public array $children = [];

    public ?string $value = null;

    public bool $isEnd = false;

    public int $weight = 0;
}
