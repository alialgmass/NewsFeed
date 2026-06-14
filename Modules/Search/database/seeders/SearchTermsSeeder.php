<?php

namespace Modules\Search\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Search\Models\SearchTerm;

class SearchTermsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $terms = [
            ['term' => 'laravel', 'frequency' => 100],
            ['term' => 'laravel installation', 'frequency' => 85],
            ['term' => 'laravel eloquent', 'frequency' => 75],
            ['term' => 'laravel api', 'frequency' => 70],
            ['term' => 'laravel authentication', 'frequency' => 65],
            ['term' => 'php', 'frequency' => 95],
            ['term' => 'php frameworks', 'frequency' => 60],
            ['term' => 'vue', 'frequency' => 90],
            ['term' => 'vue 3', 'frequency' => 80],
            ['term' => 'vue components', 'frequency' => 55],
            ['term' => 'javascript', 'frequency' => 88],
            ['term' => 'typescript', 'frequency' => 72],
            ['term' => 'tailwind', 'frequency' => 78],
            ['term' => 'tailwind css', 'frequency' => 68],
            ['term' => 'database design', 'frequency' => 50],
            ['term' => 'mysql', 'frequency' => 82],
            ['term' => 'postgresql', 'frequency' => 45],
            ['term' => 'docker', 'frequency' => 76],
            ['term' => 'docker compose', 'frequency' => 58],
            ['term' => 'docker containers', 'frequency' => 42],
            ['term' => 'redis', 'frequency' => 62],
            ['term' => 'caching', 'frequency' => 54],
            ['term' => 'queues', 'frequency' => 48],
            ['term' => 'testing', 'frequency' => 66],
            ['term' => 'phpunit', 'frequency' => 52],
            ['term' => 'pest', 'frequency' => 44],
            ['term' => 'rest api', 'frequency' => 74],
            ['term' => 'graphql', 'frequency' => 38],
            ['term' => 'websockets', 'frequency' => 36],
            ['term' => 'oauth', 'frequency' => 46],
        ];

        foreach ($terms as $term) {
            SearchTerm::create($term);
        }
    }
}
