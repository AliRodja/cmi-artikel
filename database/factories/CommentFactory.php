<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'article_id' => Article::factory()->published(),
            'user_id' => User::factory()->pembaca(),
            'body' => fake()->sentences(2, true),
        ];
    }
}
