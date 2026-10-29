<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleSeoFieldsTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Mengenal Diabetes', 'content' => 'Diabetes adalah penyakit kronis.', 'category' => 'diabetes',
            'status' => Article::STATUS_DRAFT,
        ], $overrides);
    }

    public function test_writer_can_save_seo_fields_and_form_shows_seo_panel(): void
    {
        $penulis = User::factory()->create();

        $this->actingAs($penulis)->get(route('articles.create'))
            ->assertSee('Optimasi SEO')
            ->assertSee('Pratinjau di Google')
            ->assertSee('Checklist SEO');

        $this->post(route('articles.store'), $this->payload([
            'focus_keyword' => 'gejala diabetes',
            'seo_title' => 'Gejala Diabetes yang Sering Diabaikan',
            'seo_description' => 'Kenali gejala diabetes sejak dini: sering haus, sering buang air kecil, dan luka yang lama sembuh.',
        ]))->assertSessionHasNoErrors();

        $article = Article::firstOrFail();
        $this->assertSame('gejala diabetes', $article->focus_keyword);
        $this->assertSame('Gejala Diabetes yang Sering Diabaikan', $article->seo_title);
    }

    public function test_seo_fields_follow_google_length_limits(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('articles.store'), $this->payload([
                'seo_title' => str_repeat('a', 71),
                'seo_description' => str_repeat('b', 161),
                'focus_keyword' => str_repeat('c', 101),
            ]))
            ->assertSessionHasErrors(['seo_title', 'seo_description', 'focus_keyword']);
    }

    public function test_public_page_uses_seo_title_description_and_keyword(): void
    {
        $article = Article::factory()->published()->create([
            'title' => 'Judul Artikel Asli',
            'focus_keyword' => 'gejala diabetes',
            'seo_title' => 'Gejala Diabetes yang Sering Diabaikan',
            'seo_description' => 'Kenali gejala diabetes sejak dini agar cepat ditangani.',
        ]);

        $this->get(route('public.articles.show', $article))
            ->assertSee('<title>Gejala Diabetes yang Sering Diabaikan · '.config('app.name').'</title>', false)
            ->assertSee('<meta name="description" content="Kenali gejala diabetes sejak dini agar cepat ditangani.">', false)
            ->assertSee('<meta property="og:title" content="Gejala Diabetes yang Sering Diabaikan">', false)
            ->assertSee('"keywords":"gejala diabetes"', false)
            // Judul artikel di halaman tetap judul asli; Judul SEO hanya untuk hasil Google.
            ->assertSee('Judul Artikel Asli');
    }

    public function test_empty_seo_fields_fall_back_to_title_and_content(): void
    {
        $article = Article::factory()->published()->create([
            'title' => 'Cara Menjaga Ginjal',
            'content' => 'Minum air putih yang cukup setiap hari.',
        ]);

        $this->assertSame('Cara Menjaga Ginjal', $article->meta_title);
        $this->assertSame('Minum air putih yang cukup setiap hari.', $article->meta_description);

        $this->get(route('public.articles.show', $article))
            ->assertSee('<title>Cara Menjaga Ginjal · '.config('app.name').'</title>', false);
    }
}
