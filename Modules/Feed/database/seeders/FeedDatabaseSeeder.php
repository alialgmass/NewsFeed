<?php

namespace Modules\Feed\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Feed\Models\NewCategory;
use Modules\Feed\Models\NewItem;

class FeedDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure we have categories to link to
        if (NewCategory::count() === 0) {
            $this->command->info('Creating initial categories...');
            NewCategory::factory()->count(10)->create();
            NewCategory::factory()->count(20)->withParent()->create();
        }

        $categoryIds = NewCategory::pluck('id')->toArray();

        $total = 1000000;
        $chunkSize = 1000;
        $count = 0;

        $this->command->getOutput()->progressStart($total);

        while ($count < $total) {
            // Override new_category_id to null in raw() to prevent the factory from creating 1M categories
            $rawItems = NewItem::factory()
                ->count($chunkSize)
                ->state(['new_category_id' => null])
                ->raw();

            $items = array_map(function ($item) use ($categoryIds) {
                return [
                    'title' => json_encode($item['title']),
                    'slug' => $item['slug'].'-'.bin2hex(random_bytes(8)), // Higher entropy for 1M items
                    'description' => json_encode($item['description']),
                    'body' => $item['body'],
                    'published_at' => $item['published_at'] ? ($item['published_at'] instanceof \DateTime ? $item['published_at']->format('Y-m-d H:i:s') : $item['published_at']) : null,
                    'source' => json_encode($item['source']),
                    'new_category_id' => $categoryIds[array_rand($categoryIds)],
                    'created_at' => now()->toDateTimeString(),
                    'updated_at' => now()->toDateTimeString(),
                ];
            }, $rawItems);

            NewItem::insert($items);

            $count += $chunkSize;
            $this->command->getOutput()->progressAdvance($chunkSize);
        }

        $this->command->getOutput()->progressFinish();
    }
}
