<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Article>
 */
class ArticleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'title'        => fake()->sentence(),
            'content'      => fake()->paragraph(),
            'published_at' => null,
            'user_id'      => User::factory(),
        ];
    }

    public function unpublished()
    {
        return $this->state([
            'published_at' => now()->addDay()
        ]);
    }
}
