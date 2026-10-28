<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ArticleFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->sentence(6);

        return [
            'user_id' => User::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::lower(Str::random(5)),
            'content' => fake()->paragraphs(6, true),
            'image' => null,
            'status' => fake()->randomElement([Article::STATUS_DRAFT, Article::STATUS_PUBLISHED]),
            'created_at' => fake()->dateTimeBetween('-3 months'),
        ];
    }

    public function published(): static
    {
        return $this->state(['status' => Article::STATUS_PUBLISHED]);
    }

    public function draft(): static
    {
        return $this->state(['status' => Article::STATUS_DRAFT]);
    }
}
