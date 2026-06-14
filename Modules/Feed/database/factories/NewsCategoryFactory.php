<?php

namespace Modules\Feed\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Feed\Models\NewsCategory;

class NewsCategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = NewsCategory::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'title' => [
                'en' => $this->faker->word(),
                'ar' => $this->faker->word(),
            ],
            'slug' => $this->faker->slug(),
            'description' => [
                'en' => $this->faker->sentence(),
                'ar' => $this->faker->sentence(),
            ],
            'parent_id' => null,
        ];
    }

    /**
     * Indicate that the category has a parent.
     */
    public function withParent(): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => NewsCategory::factory(),
        ]);
    }
}
