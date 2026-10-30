<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleTranslationTest extends TestCase
{
    use RefreshDatabase;

    private function english(): void
    {
        $this->post(route('locale.update'), ['locale' => 'en']);
    }

    public function test_writer_can_save_english_version_and_it_is_sanitized(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('articles.create'))->assertOk()->assertSee('Versi Bahasa Inggris (opsional)')->assertSee('content_en-editor', false);

        $this->post(route('articles.store'), [
            'title' => 'Gejala Serangan Jantung',
            'content' => '<div>Isi Indonesia.</div>',
            'title_en' => 'Heart Attack Symptoms',
            'content_en' => '<div>English content.</div><script>alert(1)</script>',
            'seo_title_en' => 'Heart Attack Symptoms You Should Know',
            'status' => Article::STATUS_DRAFT,
        ])->assertSessionHasNoErrors();

        $article = Article::firstOrFail();
        $this->assertSame('Heart Attack Symptoms', $article->title_en);
        $this->assertStringContainsString('English content.', $article->content_en);
        $this->assertStringNotContainsString('<script', $article->content_en);
        $this->assertTrue($article->hasEnglish());

        // Form edit menampilkan kembali isian Bahasa Inggris dan langkah 5 terbuka.
        $this->get(route('articles.edit', $article))
            ->assertSee('value="Heart Attack Symptoms"', false)
            ->assertSee('<details class="card group p-6"  open >', false);
    }

    public function test_english_title_and_content_must_be_filled_together(): void
    {
        $this->actingAs(User::factory()->create());
        $base = ['title' => 'Judul', 'content' => '<div>Isi.</div>', 'status' => Article::STATUS_DRAFT];

        $this->post(route('articles.store'), $base + ['title_en' => 'Only a title'])
            ->assertSessionHasErrors('content_en');
        // Editor Trix kosong tetap mengirim "<div><br></div>" dan harus dianggap kosong.
        $this->post(route('articles.store'), $base + ['title_en' => 'Only a title', 'content_en' => '<div><br></div>'])
            ->assertSessionHasErrors('content_en');
        $this->post(route('articles.store'), $base + ['content_en' => '<div>Only content.</div>'])
            ->assertSessionHasErrors('title_en');
        $this->post(route('articles.store'), $base + ['seo_title_en' => str_repeat('a', 71)])
            ->assertSessionHasErrors('seo_title_en');

        // Tanpa versi Inggris sama sekali tetap boleh; editor kosong disimpan sebagai null.
        $this->post(route('articles.store'), $base + ['content_en' => '<div><br></div>'])->assertSessionHasNoErrors();
        $this->assertNull(Article::firstOrFail()->content_en);
    }

    public function test_visitor_choosing_english_sees_the_writers_translation(): void
    {
        $article = Article::factory()->published()->create([
            'title' => 'Gejala Serangan Jantung',
            'content' => '<div>Nyeri dada adalah gejala utama.</div>',
            'title_en' => 'Heart Attack Symptoms',
            'content_en' => '<div>Chest pain is the main symptom.</div>',
        ]);

        // Bahasa Indonesia (bawaan): versi Indonesia.
        $this->get(route('public.articles.show', $article))
            ->assertSee('Gejala Serangan Jantung')
            ->assertSee('Nyeri dada adalah gejala utama.')
            ->assertDontSee('Chest pain is the main symptom.');

        $this->english();

        $this->get(route('public.articles.show', $article))
            ->assertSee('<title>Heart Attack Symptoms · ', false)
            ->assertSee('<h1 lang="en"', false)
            ->assertSee('Chest pain is the main symptom.')
            ->assertDontSee('Nyeri dada adalah gejala utama.')
            ->assertDontSee('not yet available in English');

        $this->get(route('home'))->assertSee('Heart Attack Symptoms');

        // Sidebar "Artikel Lainnya" di halaman artikel lain juga memakai judul Inggris.
        $other = Article::factory()->published()->create();
        $this->get(route('public.articles.show', $other))->assertSee('Heart Attack Symptoms');
    }

    public function test_article_without_translation_falls_back_to_indonesian_with_notice(): void
    {
        $article = Article::factory()->published()->create(['title' => 'Gejala Serangan Jantung', 'title_en' => null, 'content_en' => null]);

        $this->english();

        $this->get(route('public.articles.show', $article))
            ->assertSee('<h1 lang="id"', false)
            ->assertSee('Gejala Serangan Jantung')
            ->assertSee('This article is not yet available in English, so it is shown in Indonesian.');
    }

    public function test_search_also_matches_english_text(): void
    {
        Article::factory()->published()->create([
            'title' => 'Gejala Serangan Jantung',
            'title_en' => 'Heart Attack Symptoms',
            'content_en' => '<div>Chest pain.</div>',
        ]);

        $this->get(route('home', ['search' => 'Heart Attack']))->assertSee('Gejala Serangan Jantung');

        $this->english();
        $this->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->get(route('articles.live-search', ['q' => 'Heart Attack']))
            ->assertOk()
            ->assertSee('1 article found')
            // Kata yang dicari disorot <mark>, jadi cek bagian judul di luar sorotan.
            ->assertSee('<mark class=', false)
            ->assertSee('Symptoms');
    }

    public function test_admin_detail_shows_english_status_and_preview(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $translated = Article::factory()->inReview()->withEnglish()->create(['title_en' => 'Heart Attack Symptoms']);
        $untranslated = Article::factory()->inReview()->create(['title_en' => null, 'content_en' => null]);

        $this->get(route('articles.show', $translated))
            ->assertSee('Versi Bahasa Inggris')
            ->assertSee('Tersedia')
            ->assertSee('Pratinjau versi Bahasa Inggris')
            ->assertSee('Heart Attack Symptoms');

        $this->get(route('articles.show', $untranslated))->assertDontSee('Pratinjau versi Bahasa Inggris');
    }
}
