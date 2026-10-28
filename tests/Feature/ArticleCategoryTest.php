<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleCategoryTest extends TestCase
{
    use RefreshDatabase;

    private const CS_WHATSAPP_URL = 'https://api.whatsapp.com/send/?phone=6282121590000&text=Halo%2C+dari+website+cmihospital.com%2C+Saya+ingin+konsultasi.%0ANama%3A+%0AAlamat%3A+%0AKeluhan%3A+&app_absent=0';

    public function test_category_is_required_and_must_be_one_of_the_four(): void
    {
        $penulis = User::factory()->create();
        $payload = ['title' => 'Kenali Gejala Gagal Ginjal', 'content' => 'Isi.', 'status' => Article::STATUS_DRAFT];

        $this->actingAs($penulis)->post(route('articles.store'), $payload)->assertSessionHasErrors('category');
        $this->post(route('articles.store'), $payload + ['category' => 'flu'])->assertSessionHasErrors('category');
        $this->assertDatabaseCount('articles', 0);

        $this->post(route('articles.store'), $payload + ['category' => 'ginjal'])->assertSessionHasNoErrors();
        $this->assertSame('Ginjal', Article::firstOrFail()->category_label);
    }

    public function test_home_can_be_filtered_by_category(): void
    {
        Article::factory()->published()->create(['title' => 'Artikel Jantung Sehat', 'category' => 'jantung']);
        Article::factory()->published()->create(['title' => 'Artikel Diabetes Muda', 'category' => 'diabetes']);

        $this->get(route('home', ['jenis' => 'jantung']))
            ->assertSee('Artikel Jantung Sehat')
            ->assertDontSee('Artikel Diabetes Muda');

        // Jenis yang tidak dikenal diabaikan → semua artikel tampil.
        $this->get(route('home', ['jenis' => 'ngawur']))
            ->assertSee('Artikel Jantung Sehat')
            ->assertSee('Artikel Diabetes Muda');
    }

    public function test_article_page_has_share_links_category_sidebar_and_whatsapp_consultation(): void
    {
        $article = Article::factory()->published()->create(['title' => 'Nyeri Dada & Serangan Jantung', 'category' => 'jantung']);
        Article::factory(2)->published()->create(['category' => 'jantung']);
        Article::factory()->draft()->create(['category' => 'kanker']);
        $url = route('public.articles.show', $article);

        $this->get($url)
            ->assertOk()
            // Bagikan: judul & URL di-encode di query string.
            ->assertSee('https://wa.me/?text='.urlencode($article->title."\n".$url), false)
            ->assertSee('https://x.com/intent/post?text='.urlencode($article->title).'&amp;url='.urlencode($url), false)
            ->assertSee('https://www.facebook.com/sharer/sharer.php?u='.urlencode($url), false)
            // Sidebar: jumlah per jenis hanya menghitung artikel Published.
            ->assertSeeInOrder(['Jenis Artikel', 'Diabetes', '0', 'Jantung', '3', 'Kanker', '0', 'Ginjal', '0'])
            ->assertSee('Alami gejala jantung?')
            ->assertSee(e(self::CS_WHATSAPP_URL), false);
    }

    public function test_old_article_without_category_still_renders(): void
    {
        $article = Article::factory()->published()->create(['category' => null]);

        $this->get(route('public.articles.show', $article))
            ->assertOk()
            ->assertSee('Punya keluhan kesehatan?');
    }
}
