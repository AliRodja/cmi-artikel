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
        // Halaman dipakai nakes & editor konten: tanpa istilah teknis database.
        $response->assertSee('Tanpa penulis')->assertDontSee('INNER JOIN');
    }

    public function test_report_shows_range_highlight_and_print_button(): void
    {
        $author = User::factory()->create(['name' => 'Rina Gizi']);
        Article::factory(12)->for($author)->published()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('articles.report', ['search' => 'Rina']))
            ->assertSee('Menampilkan <strong class="text-gray-900">1–10</strong>', false)
            ->assertSee('<mark class="rounded-sm bg-amber-100 px-0.5 text-inherit">Rina</mark>', false)
            ->assertSee('aria-sort="descending"', false)
            ->assertSee('window.print()', false);
    }

    public function test_dashboard_article_detail_has_preview_info_completeness_and_google_preview(): void
    {
        $penulis = User::factory()->create(['name' => 'Rina', 'profession' => 'Ahli Gizi']);
        $article = Article::factory()->for($penulis)->inReview()->create([
            'title' => 'Mengenal Gejala Diabetes',
            'content' => str_repeat('kata ', 320),
            'image' => null,
            'sources' => 'Kemenkes RI',
            'focus_keyword' => 'gejala diabetes',
            'medical_reviewer_id' => null,
        ]);

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('articles.show', $article))
            ->assertOk()
            ->assertSee('Tinjau artikel ini')
            ->assertSee('menunggu keputusan Anda', false)
            ->assertSeeInOrder(['Informasi artikel', 'Rina', 'Ahli Gizi', '320 kata'])
            // 4 dari 6: belum ada gambar & belum ditinjau tenaga medis.
            ->assertSee('4/6')
            ->assertSee('Belum ada gambar utama.')
            ->assertSee('Tampilan di Google')
            ->assertSee('gejala diabetes')
            ->assertSee('Pratinjau artikel');
    }

    public function test_reading_time_is_not_shown_anywhere(): void
    {
        $pembaca = User::factory()->pembaca()->create();
        $article = Article::factory()->published()->create(['content' => str_repeat('kata ', 600)]);
        $pembaca->bookmarks()->attach($article);

        $this->get(route('home'))->assertDontSee('menit baca');
        $this->get(route('public.articles.show', $article))->assertDontSee('menit baca');
        $this->actingAs($pembaca)->get(route('bookmarks.index'))->assertDontSee('menit baca');
        $this->actingAs(User::factory()->admin()->create())->get(route('articles.show', $article))->assertDontSee('menit baca');
    }

    public function test_write_form_has_numbered_steps_word_count_and_completeness(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('articles.create'))
            ->assertSeeInOrder(['Judul artikel', 'Isi artikel', 'Sumber rujukan', 'Optimasi SEO (opsional)'], false)
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
