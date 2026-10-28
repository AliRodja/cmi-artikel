<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::factory()->admin()->create([
            'name' => 'Admin Demo',
            'email' => 'admin@example.com',
        ]);

        Article::factory(20)->for($admin)->create();

        // Penulis demo: contoh setiap tahap alur review.
        $penulis = User::factory()->create([
            'name' => 'Penulis Demo',
            'email' => 'penulis@example.com',
        ]);

        Article::factory(3)->for($penulis)->published()->create();
        Article::factory(2)->for($penulis)->inReview()->create();
        Article::factory()->for($penulis)->draft()->create([
            'review_note' => 'Mohon tambahkan sumber rujukan medis di akhir artikel sebelum diajukan lagi.',
        ]);

        Article::factory(10)->create();

        Article::factory(3)->create([
            'user_id' => null,
            'status' => Article::STATUS_PUBLISHED,
        ]);
    }
}
