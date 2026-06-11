<?php

namespace Modules\Feed\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Feed\Models\NewItem;
use Modules\Feed\Models\NewCategory;

class NewItemFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = NewItem::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'title' => [
                'en' => $this->faker->sentence(),
                'ar' => $this->faker->sentence(), // Assuming Arabic is the second language
            ],
            'slug' => $this->faker->slug(),
            'description' => [
                'en' => $this->faker->paragraph(),
                'ar' => $this->faker->paragraph(),
            ],
            'body' => $this->faker->paragraphs(3, true),
            'published_at' => $this->faker->optional()->dateTimeBetween('-1 year', 'now'),
            'source' => [
                'url' => $this->faker->url(),
                'name' => $this->faker->company(),
            ],
            'new_category_id' => NewCategory::factory(),
        ];
    }

    /**
     * Indicate that the item belongs to a subcategory.
     */
    public function withSubCategory(): static
    {
        return $this->state(fn (array $attributes) => [
            'new_category_id' => NewCategory::factory()->withParent(),
        ]);
    }

    /**
     * Configure the factory.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (NewItem $newItem) {
            $url = 'https://picsum.photos/640/480';
            try {
                $newItem->addMediaFromUrl($url)
                    ->toMediaCollection('cover');
            } catch (\Exception $e) {
                // Ignore media errors in factory
            }
        });
    }
}

