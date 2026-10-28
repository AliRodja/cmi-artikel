<?php

namespace Tests\Feature;

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LiveSearchTest extends TestCase
{
    use RefreshDatabase;

    /** Permintaan seperti dari fetch() di resources/js/live-search.js. */
    private function liveSearch(string $term)
    {
        return $this->get(route('articles.live-search', ['q' => $term]), ['X-Requested-With' => 'XMLHttpRequest']);
    }

    public function test_navbar_has_live_search_panel(): void
    {
        $this->get(route('home'))
            ->assertSee('data-live-search-url="'.route('articles.live-search').'"', false)
            ->assertSee('role="combobox"', false);
    }

    public function test_returns_published_matches_with_highlight_and_title_matches_first(): void
    {
        Article::factory()->published()->create(['title' => 'Pola Makan Sehat', 'content' => 'Kurangi gula untuk cegah diabetes.', 'created_at' => now()]);
        Article::factory()->published()->create(['title' => 'Mengenal Diabetes Tipe 2', 'content' => 'Isi.', 'created_at' => now()->subYear()]);
        Article::factory()->draft()->create(['title' => 'Draft Diabetes Rahasia']);

        $response = $this->liveSearch('diabetes')
            ->assertOk()
            ->assertSee('2 artikel ditemukan')
            ->assertSee('<mark class="rounded-sm bg-amber-100 px-0.5 text-inherit">Diabetes</mark>', false)
            ->assertDontSee('Draft Diabetes Rahasia');

        // Walau lebih lama, artikel yang judulnya cocok tampil sebelum yang hanya cocok di isi.
        $this->assertSame(
            ['Mengenal Diabetes Tipe 2', 'Pola Makan Sehat'],
            $response->viewData('results')->pluck('title')->all(),
        );
    }

    public function test_results_are_limited_and_link_to_full_results(): void
    {
        Article::factory(8)->published()->create(['title' => 'Jantung Sehat']);

        $response = $this->liveSearch('jantung')->assertSee('8 artikel ditemukan');

        $this->assertCount(6, $response->viewData('results'));
        $response->assertSee(e(route('home', ['search' => 'jantung'])).'#daftar-artikel', false);
    }

    public function test_title_is_escaped_even_when_highlighted(): void
    {
        Article::factory()->published()->create(['title' => 'Bahaya <script>alert(1)</script> gula']);

        $this->liveSearch('gula')
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('&lt;script&gt;', false);
    }

    public function test_short_or_unknown_terms_show_empty_state(): void
    {
        Article::factory()->published()->create(['title' => 'Kanker Serviks']);

        $this->assertCount(0, $this->liveSearch('k')->viewData('results'));
        $this->liveSearch('xyzabc')->assertSee('Tidak ada artikel untuk');
    }

    public function test_opening_endpoint_directly_redirects_to_full_results(): void
    {
        $this->get(route('articles.live-search', ['q' => 'ginjal']))
            ->assertRedirect(route('home', ['search' => 'ginjal']));
    }
}
