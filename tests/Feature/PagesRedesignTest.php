<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use App\Notifications\NewArticlePublished;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PagesRedesignTest extends TestCase
{
    use RefreshDatabase;

    public function test_404_page_is_indonesian_noindex_and_suggests_articles(): void
    {
        $article = Article::factory()->published()->create(['title' => 'Saran Bacaan Jantung']);

        $this->get('/halaman-yang-tidak-ada')
            ->assertNotFound()
            ->assertSee('Halaman tidak ditemukan')
            ->assertSee('<meta name="robots" content="noindex, follow">', false)
            ->assertSee('Saran Bacaan Jantung');

        // Artikel Draft juga 404 dan tidak boleh diindeks walau route-nya halaman publik.
        $draft = Article::factory()->draft()->create();
        $this->get(route('public.articles.show', $draft))
            ->assertNotFound()
            ->assertSee('<meta name="robots" content="noindex, follow">', false);
    }

    public function test_too_many_login_attempts_show_indonesian_429_page(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => 'siapa@cmi.test', 'password' => 'salah']);
        }

        $this->post('/login', ['email' => 'siapa@cmi.test', 'password' => 'salah'])
            ->assertStatus(429)
            ->assertSee('Terlalu banyak percobaan');
    }

    public function test_server_error_page_renders_without_database_or_navbar(): void
    {
        $html = view('errors.500')->render();

        $this->assertStringContainsString('Terjadi kesalahan', $html);
        $this->assertStringNotContainsString('popovertarget', $html, 'Layout minimal tanpa navbar (tanpa query user/notifikasi).');
    }

    public function test_read_later_list_has_remove_button_and_saved_date(): void
    {
        $pembaca = User::factory()->pembaca()->create();
        $article = Article::factory()->published()->create(['title' => 'Artikel Disimpan']);
        $pembaca->bookmarks()->attach($article);

        $this->actingAs($pembaca)
            ->get(route('bookmarks.index'))
            ->assertSee('1 artikel tersimpan')
            ->assertSee('Disimpan')
            ->assertSee('action="'.route('bookmarks.toggle', $article).'"', false);

        $this->from(route('bookmarks.index'))
            ->post(route('bookmarks.toggle', $article))
            ->assertRedirect(route('bookmarks.index'));
        $this->assertFalse($pembaca->hasBookmarked($article));
    }

    public function test_notifications_can_be_filtered_to_unread(): void
    {
        $pembaca = User::factory()->pembaca()->create();
        $read = Article::factory()->published()->create(['title' => 'Sudah Dibaca']);
        $unread = Article::factory()->published()->create(['title' => 'Belum Dibaca']);
        $pembaca->notify(new NewArticlePublished($read));
        $pembaca->notifications()->first()->markAsRead();
        $pembaca->notify(new NewArticlePublished($unread));

        // Dicek lewat data halaman, karena dropdown lonceng di navbar memang menampilkan 5 notifikasi terakhir.
        $response = $this->actingAs($pembaca)
            ->get(route('notifications.index', ['filter' => 'belum-dibaca']))
            ->assertSee('Hari ini');

        $this->assertSame(['Belum Dibaca'], collect($response->viewData('notifications')->items())->pluck('data.title')->all());
        $this->assertSame(1, $response->viewData('unreadCount'));
        $this->assertSame(2, $response->viewData('totalCount'));
    }

    public function test_report_summary_shows_articles_filtered_by_inner_join(): void
    {
        Article::factory(3)->published()->create();
        Article::factory(2)->published()->create(['user_id' => null]);

        $response = $this->actingAs(User::factory()->admin()->create())->get(route('articles.report'));

        $this->assertSame(5, $response->viewData('publishedTotal'));
        $this->assertSame(2, $response->viewData('withoutAuthor'));
        $this->assertSame(3, $response->viewData('articles')->total());
        $response->assertSee('Tersaring INNER JOIN');
    }

    public function test_write_form_has_numbered_steps_word_count_and_completeness(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('articles.create'))
            ->assertSeeInOrder(['Judul &amp; jenis artikel', 'Isi artikel', 'Sumber rujukan', 'Optimasi SEO (opsional)'], false)
            ->assertSee('data-word-count', false)
            ->assertSee('Kelengkapan artikel')
            ->assertSee('Isi minimal 300 kata');
    }

    public function test_dashboard_shows_total_views_and_clickable_stats(): void
    {
        $penulis = User::factory()->create();
        Article::factory()->for($penulis)->published()->create(['view_count' => 12, 'title' => 'Paling Ramai']);
        Article::factory()->for($penulis)->draft()->create(['view_count' => 0]);

        $this->actingAs($penulis)
            ->get(route('dashboard'))
            ->assertSee('12 kali')
            ->assertSee('Paling banyak dibaca')
            ->assertSee('Paling Ramai')
            ->assertSee(route('articles.index', ['status' => Article::STATUS_DRAFT]));
    }
}
