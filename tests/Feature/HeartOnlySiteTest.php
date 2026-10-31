<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/** Situs khusus artikel kesehatan jantung: tidak ada lagi jenis/kategori artikel. */
class HeartOnlySiteTest extends TestCase
{
    use RefreshDatabase;

    private const CS_WHATSAPP_URL = 'https://api.whatsapp.com/send/?phone=6282121590000&text=Halo%2C+dari+website+cmihospital.com%2C+Saya+ingin+konsultasi.%0ANama%3A+%0AAlamat%3A+%0AKeluhan%3A+&app_absent=0';

    public function test_articles_no_longer_have_a_category(): void
    {
        $this->assertFalse(Schema::hasColumn('articles', 'category'));

        $this->actingAs(User::factory()->create())
            ->get(route('articles.create'))
            ->assertDontSee('Jenis Artikel')
            ->assertDontSee('name="category"', false);

        // Field "category" yang dikirim paksa diabaikan (tidak ada di aturan validasi maupun $fillable).
        $this->post(route('articles.store'), [
            'title' => 'Kenali Gejala Serangan Jantung', 'content' => 'Isi.', 'status' => Article::STATUS_DRAFT, 'category' => 'kanker',
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('articles', ['title' => 'Kenali Gejala Serangan Jantung']);
    }

    public function test_home_is_about_heart_health_without_category_filter(): void
    {
        Article::factory()->published()->create(['title' => 'Nyeri Dada Bukan Selalu Maag']);

        $this->get(route('home'))
            ->assertSee('<title>Artikel Kesehatan Jantung · '.config('app.name').'</title>', false)
            ->assertSee('Nyeri Dada Bukan Selalu Maag')
            ->assertSee('data-hero-slider', false)
            ->assertSee('images/Hero-1.webp', false)
            ->assertSee('images/Hero-2.webp', false)
            ->assertSee('images/Hero-3.webp', false)
            ->assertSee('data-hero-prev', false)
            ->assertSee('data-hero-next', false)
            ->assertDontSee('?jenis=', false)
            ->assertDontSee('Berita Utama');
    }

    public function test_article_page_has_share_links_and_heart_consultation(): void
    {
        $article = Article::factory()->published()->create(['title' => 'Nyeri Dada & Serangan Jantung']);
        $url = route('public.articles.show', $article);

        $this->get($url)
            ->assertOk()
            // Bagikan: judul & URL di-encode di query string.
            ->assertSee('https://wa.me/?text='.urlencode($article->title."\n".$url), false)
            ->assertSee('https://x.com/intent/post?text='.urlencode($article->title).'&amp;url='.urlencode($url), false)
            ->assertSee('https://www.facebook.com/sharer/sharer.php?u='.urlencode($url), false)
            ->assertDontSee('Jenis Artikel')
            ->assertSee('Alami gejala jantung?')
            ->assertSee(e(self::CS_WHATSAPP_URL), false);
    }

    public function test_migration_turns_non_heart_articles_into_drafts_before_dropping_the_column(): void
    {
        $migration = require database_path('migrations/2026_10_29_151224_drop_category_from_articles_table.php');
        $migration->down();

        $heart = Article::factory()->published()->create();
        $cancer = Article::factory()->published()->create();
        $old = Article::factory()->published()->create();
        DB::table('articles')->where('id', $heart->id)->update(['category' => 'jantung']);
        DB::table('articles')->where('id', $cancer->id)->update(['category' => 'kanker']);

        $migration->up();

        $this->assertTrue($heart->fresh()->isPublished());
        $this->assertSame(Article::STATUS_DRAFT, $cancer->fresh()->status, 'Bukan artikel jantung → Draft, tidak dihapus.');
        $this->assertTrue($old->fresh()->isPublished(), 'Artikel lama tanpa jenis tidak diubah.');
        $this->assertFalse(Schema::hasColumn('articles', 'category'));
    }
}
