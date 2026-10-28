<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\ArticleView;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

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

        // Pembaca demo: berkomentar & menyimpan artikel (Baca Nanti).
        $pembaca = User::factory()->pembaca()->create([
            'name' => 'Pembaca Demo',
            'email' => 'pembaca@example.com',
        ]);

        $published = Article::published()->whereNotNull('user_id')->inRandomOrder()->take(5)->get();

        foreach ($published as $article) {
            Comment::factory()->for($article)->for($pembaca)->create();
        }

        $pembaca->bookmarks()->attach($published->take(3)->pluck('id'));

        // Riwayat kunjungan acak, agar "Artikel Terpopuler" sudah berisi pada instalasi baru.
        // view_count disamakan dengan jumlah riwayatnya (lewat query builder agar updated_at tidak berubah).
        Article::published()->each(function (Article $article) {
            $views = ArticleView::factory(fake()->numberBetween(0, 40))->for($article)->create();

            DB::table('articles')->where('id', $article->id)->update(['view_count' => $views->count()]);
        });
    }
}
