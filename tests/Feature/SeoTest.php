<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    /** Ambil isi JSON-LD pertama di halaman sebagai array. */
    private function structuredData(string $html): array
    {
        preg_match('~<script type="application/ld\+json">(.*?)</script>~s', $html, $match);

        return json_decode($match[1] ?? 'null', true, flags: JSON_THROW_ON_ERROR);
    }

    public function test_article_page_has_meta_description_open_graph_and_canonical(): void
    {
        $article = Article::factory()->published()->create([
            'title' => 'Cegah Diabetes Sejak Dini',
            'content' => 'Kurangi gula dan rutin berolahraga setiap hari.',
            'image' => 'articles/foto.jpg',
        ]);
        $url = route('public.articles.show', $article);

        $this->get($url.'?utm_source=whatsapp')
            ->assertSee('<meta name="description" content="Kurangi gula dan rutin berolahraga setiap hari.">', false)
            ->assertSee('<meta name="robots" content="index, follow, max-image-preview:large">', false)
            ->assertSee('<link rel="canonical" href="'.$url.'">', false)
            ->assertSee('<meta property="og:type" content="article">', false)
            ->assertSee('<meta property="og:title" content="Cegah Diabetes Sejak Dini">', false)
            ->assertSee('<meta property="og:image" content="'.asset('storage/articles/foto.jpg').'">', false)
            ->assertSee('article:published_time', false);
    }

    public function test_article_without_image_uses_default_share_image(): void
    {
        $article = Article::factory()->published()->create(['image' => null]);

        $this->get(route('public.articles.show', $article))
            ->assertSee('<meta property="og:image" content="'.asset('images/og-default.png').'">', false);

        $this->assertFileExists(public_path('images/og-default.png'));
    }

    public function test_article_structured_data_has_author_reviewer_and_breadcrumbs(): void
    {
        $author = User::factory()->create(['name' => 'Rina', 'profession' => 'Ahli Gizi']);
        $doctor = User::factory()->create(['name' => 'dr. Andi', 'profession' => 'Dokter Spesialis Jantung']);
        $article = Article::factory()->for($author)->published()->create([
            'title' => 'Judul </script><script>alert(1)</script>',
            'medical_reviewer_id' => $doctor->id,
        ]);

        $response = $this->get(route('public.articles.show', $article));

        // Judul berisi </script> tidak bisa keluar dari tag JSON-LD (JSON_HEX_TAG).
        $response->assertDontSee('</script><script>alert(1)', false);

        $graph = collect($this->structuredData($response->getContent())['@graph'])->keyBy('@type');
        $this->assertSame('Judul </script><script>alert(1)</script>', $graph['Article']['headline']);
        $this->assertSame(['@type' => 'Person', 'name' => 'Rina', 'jobTitle' => 'Ahli Gizi'], $graph['Article']['author']);
        $this->assertSame('dr. Andi', $graph['MedicalWebPage']['reviewedBy']['name']);
        $this->assertSame(['Beranda', $article->title], array_column($graph['BreadcrumbList']['itemListElement'], 'name'));
        $this->assertSame('https://schema.org/Cardiovascular', $graph['MedicalWebPage']['specialty']);
    }

    public function test_home_canonical_keeps_page_but_drops_tracking(): void
    {
        $this->get(route('home', ['page' => 2, 'utm_source' => 'x']))
            ->assertSee('<link rel="canonical" href="'.route('home', ['page' => 2]).'">', false)
            ->assertSee('<title>Artikel Kesehatan Jantung – Halaman 2 · '.config('app.name').'</title>', false);

        $this->get(route('home'))
            ->assertSee('"@type":"MedicalClinic"', false)
            ->assertSee('"@type":"SearchAction"', false);
    }

    public function test_private_and_search_pages_are_noindex(): void
    {
        $noindex = '<meta name="robots" content="noindex, follow">';

        $this->get(route('home', ['search' => 'gula']))->assertSee($noindex, false);
        $this->get(route('login'))->assertSee($noindex, false);
        $this->actingAs(User::factory()->create())->get(route('dashboard'))->assertSee($noindex, false);
        $this->get(route('profile.edit'))->assertSee($noindex, false);
    }

    public function test_sitemap_lists_home_and_published_articles(): void
    {
        $published = Article::factory()->published()->create(['title' => 'Tom & Jerry']);
        $draft = Article::factory()->draft()->create();

        $response = $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8');

        $locations = [];
        foreach (simplexml_load_string($response->getContent())->url as $url) {
            $locations[] = (string) $url->loc;
        }

        $this->assertContains(route('home'), $locations);
        $this->assertContains(route('public.articles.show', $published), $locations);
        $this->assertCount(2, $locations, 'Hanya beranda + artikel Published (tidak ada lagi halaman per jenis).');
        $this->assertNotContains(route('public.articles.show', $draft), $locations);
    }

    public function test_robots_txt_blocks_everything_outside_production(): void
    {
        $this->get('/robots.txt')->assertOk()->assertSee('Disallow: /');

        $this->app->detectEnvironment(fn () => 'production');

        $this->get('/robots.txt')
            ->assertSee('Disallow: /dashboard')
            ->assertSee('Sitemap: '.route('sitemap'))
            ->assertDontSee("Disallow: /\n", false);
    }
}
