<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin Demo',
            'email' => 'admin@example.com',
        ]);

        Article::factory(20)->for($admin)->create();
        Article::factory(10)->create();

        Article::factory(3)->create([
            'user_id' => null,
            'status' => Article::STATUS_PUBLISHED,
        ]);
    }
}
