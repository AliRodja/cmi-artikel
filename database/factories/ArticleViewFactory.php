<?php

namespace Database\Factories;

use App\Models\Article;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ArticleViewFactory extends Factory
{
    public function definition(): array
    {
        return [
            'article_id' => Article::factory()->published(),
            'user_id' => null,
            'visitor' => 'guest:'.hash('sha256', Str::random(40)),
            'viewed_at' => fake()->dateTimeBetween('-30 days'),
        ];
    }
}
