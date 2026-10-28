<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ArticleViewTest extends TestCase
{
    use RefreshDatabase;

    private Article $article;

    protected function setUp(): void
    {
        parent::setUp();

        $this->article = Article::factory()->published()->create(['category' => 'jantung']);
    }

    private function visitAsGuest(string $sessionId): void
    {
        // Cookie sesi yang sama = pengunjung yang sama.
        $this->withCookie(config('session.cookie'), $sessionId)
            ->get(route('public.articles.show', $this->article))
            ->assertOk();
    }

    public function test_guest_is_counted_once_per_session_and_history_is_recorded(): void
    {
        $sessionA = Str::random(40);

        $this->visitAsGuest($sessionA);
        $this->visitAsGuest($sessionA);
        $this->assertSame(1, $this->article->fresh()->view_count, 'Refresh tidak menambah angka.');

        $this->visitAsGuest(Str::random(40));
        $this->assertSame(2, $this->article->fresh()->view_count);

        $this->assertDatabaseCount('article_views', 2);
        $this->assertDatabaseMissing('article_views', ['visitor' => 'guest:'.$sessionA]);
        $this->assertDatabaseHas('article_views', ['visitor' => 'guest:'.hash('sha256', $sessionA), 'user_id' => null]);
    }

    public function test_logged_in_user_is_counted_once_but_author_is_not_counted(): void
    {
        $pembaca = User::factory()->pembaca()->create();

        $this->actingAs($pembaca)->get(route('public.articles.show', $this->article));
        $this->actingAs($pembaca)->get(route('public.articles.show', $this->article));
        $this->assertSame(1, $this->article->fresh()->view_count);
        $this->assertDatabaseHas('article_views', ['user_id' => $pembaca->id, 'visitor' => "user:{$pembaca->id}"]);

        $this->actingAs($this->article->user)->get(route('public.articles.show', $this->article));
        $this->assertSame(1, $this->article->fresh()->view_count, 'Penulis membuka artikelnya sendiri: tidak dihitung.');
    }

    public function test_view_does_not_change_updated_at_and_drafts_are_not_counted(): void
    {
        $this->article->timestamps = false;
        $this->article->forceFill(['updated_at' => now()->subWeek()])->save();
        $updatedAt = $this->article->fresh()->updated_at;

        $this->get(route('public.articles.show', $this->article))->assertSee('1 kali dilihat');
        $this->assertEquals($updatedAt, $this->article->fresh()->updated_at);

        $draft = Article::factory()->draft()->create();
        $this->get(route('public.articles.show', $draft))->assertNotFound();
        $this->assertSame(0, $draft->fresh()->view_count);
    }

    public function test_popular_articles_need_minimum_views_and_are_sorted_by_views(): void
    {
        config(['clinic.popular_min_views' => 5]);

        Article::factory()->published()->create(['title' => 'Populer Kedua', 'view_count' => 10]);
        Article::factory()->published()->create(['title' => 'Populer Pertama', 'view_count' => 50]);
        Article::factory()->published()->create(['title' => 'Belum Cukup', 'view_count' => 4]);
        Article::factory()->draft()->create(['title' => 'Draft Ramai', 'view_count' => 99]);
        $this->article->forceFill(['view_count' => 80])->save();

        $popular = $this->get(route('public.articles.show', $this->article))->viewData('popularArticles');

        // Artikel yang sedang dibuka tidak ikut, walaupun paling ramai.
        $this->assertSame(['Populer Pertama', 'Populer Kedua'], $popular->pluck('title')->all());
    }

    public function test_home_shows_popular_articles_only_on_landing_page(): void
    {
        config(['clinic.popular_min_views' => 5]);
        $this->article->forceFill(['view_count' => 30])->save();
        Article::factory()->published()->create(['view_count' => 2]);

        $popular = $this->get(route('home'))
            ->assertSee('Terpopuler')
            ->assertSee(e(config('clinic.whatsapp_consultation_url')), false)
            ->viewData('popularArticles');
        $this->assertSame([$this->article->id], $popular->pluck('id')->all());

        // Saat mencari / memfilter, fokus ke hasil: Terpopuler tidak ditampilkan.
        $this->get(route('home', ['jenis' => 'jantung']))->assertDontSee('Terpopuler');
        $this->get(route('home', ['search' => 'apa saja']))->assertDontSee('Terpopuler');
    }

    public function test_home_has_news_portal_sections_only_on_landing_page(): void
    {
        $newest = Article::factory()->published()->create(['title' => 'Kabar Terbaru Ginjal', 'category' => 'ginjal', 'created_at' => now()->addMinute()]);

        $response = $this->get(route('home'))
            ->assertSeeInOrder(['Sorotan', 'Kabar Terbaru Ginjal', 'Artikel Lainnya', 'Berita Utama', 'Terpopuler', 'Semua Artikel']);

        // Kotak besar = artikel terbaru; Berita Utama hanya berisi jenis yang punya artikel.
        $this->assertTrue($response->viewData('latestArticles')->first()->is($newest));
        $this->assertSame(['jantung', 'ginjal'], $response->viewData('categorySections')->keys()->all());

        $this->get(route('home', ['jenis' => 'ginjal']))->assertDontSee('Berita Utama')->assertDontSee('Sorotan');
    }

    public function test_related_articles_have_the_same_category(): void
    {
        Article::factory()->published()->create(['title' => 'Jantung Lain', 'category' => 'jantung']);
        Article::factory()->published()->create(['title' => 'Soal Ginjal', 'category' => 'ginjal']);
        Article::factory()->draft()->create(['title' => 'Jantung Draft', 'category' => 'jantung']);

        $related = $this->get(route('public.articles.show', $this->article))->viewData('relatedArticles');

        $this->assertSame(['Jantung Lain'], $related->pluck('title')->all());
    }

    public function test_writer_sees_visit_statistics_and_history(): void
    {
        $pembaca = User::factory()->pembaca()->create(['name' => 'Rani Pembaca']);
        $this->actingAs($pembaca)->get(route('public.articles.show', $this->article));
        $this->post('/logout');
        $this->get(route('public.articles.show', $this->article));

        $this->actingAs($this->article->user)
            ->get(route('articles.show', $this->article))
            ->assertSee('Statistik Kunjungan')
            ->assertSee('Rani Pembaca')
            ->assertSee('Pengunjung tamu');
    }
}
