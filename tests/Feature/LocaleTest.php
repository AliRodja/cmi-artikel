<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use App\Support\ArticleNotifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class LocaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_site_is_indonesian_by_default_with_language_button(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('<html lang="id">', false)
            ->assertSee('popovertarget="locale-menu"', false)
            ->assertSee('Artikel Kesehatan Jantung')
            ->assertSee('Masuk');
    }

    public function test_visitor_can_switch_to_english_and_it_persists(): void
    {
        $article = Article::factory()->published()->create(['title' => 'Mengenal Serangan Jantung']);

        $this->from(route('public.articles.show', $article))
            ->post(route('locale.update'), ['locale' => 'en'])
            ->assertRedirect(route('public.articles.show', $article));

        $this->get(route('home'))
            ->assertSee('<html lang="en">', false)
            ->assertSee('Heart Health Articles')
            ->assertSee('Sign in')
            ->assertDontSee('Artikel Kesehatan Jantung</h1>', false);

        // Antarmuka Inggris, isi artikel tetap Indonesia (ditandai lang="id").
        $this->get(route('public.articles.show', $article))
            ->assertSee('Mengenal Serangan Jantung')
            ->assertSee('<div lang="id" class="article-content', false)
            ->assertSee('Comments')
            ->assertSee('Articles are written in Indonesian.');

        $this->post(route('locale.update'), ['locale' => 'id']);
        $this->get(route('home'))->assertSee('<html lang="id">', false)->assertSee('Masuk');
    }

    public function test_unknown_language_is_rejected(): void
    {
        $this->post(route('locale.update'), ['locale' => 'fr'])->assertSessionHasErrors('locale');

        $this->get(route('home'))->assertSee('<html lang="id">', false);
    }

    public function test_writer_area_follows_chosen_language(): void
    {
        $penulis = User::factory()->create(['name' => 'Rina']);
        Article::factory()->for($penulis)->draft()->create();

        $this->actingAs($penulis)->post(route('locale.update'), ['locale' => 'en']);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('<html lang="en">', false)
            ->assertSee('popovertarget="locale-menu"', false)
            ->assertSee('Hello, Rina')
            ->assertSee('Write Article');

        $this->get(route('articles.index'))->assertSee('My Articles')->assertSee('Draft');
        $this->get(route('articles.create'))->assertSee('Article title')->assertSee('SEO checklist');

        // Pesan validasi Form Request ikut bahasa, termasuk nama kolomnya.
        $this->post(route('articles.store'), ['title' => '', 'content' => '', 'status' => 0])
            ->assertSessionHasErrors(['title' => 'The title field is required.']);
    }

    public function test_admin_pages_and_notifications_follow_chosen_language(): void
    {
        $admin = User::factory()->admin()->create();
        $penulis = User::factory()->create(['name' => 'Rina']);
        $article = Article::factory()->for($penulis)->create(['status' => Article::STATUS_REVIEW]);
        ArticleNotifier::submitted($article);

        $this->actingAs($admin)->post(route('locale.update'), ['locale' => 'en']);

        $this->get(route('review.index'))->assertSee('Review Articles')->assertSee('Awaiting Review');
        $this->get(route('articles.report'))->assertSee('Article Report')->assertSee('Print');
        $this->get(route('users.index'))->assertSee('Manage Users')->assertSee('Add User');
        // Notifikasi disimpan sebagai templat + data, jadi tampil dalam bahasa pembaca.
        $this->get(route('notifications.index'))->assertSee('Rina submitted an article for review.');

        $this->post(route('locale.update'), ['locale' => 'id']);
        $this->get(route('notifications.index'))->assertSee('Rina mengajukan artikel untuk direview.');
    }

    public function test_not_found_pages_follow_chosen_language(): void
    {
        $draft = Article::factory()->draft()->create();
        $this->post(route('locale.update'), ['locale' => 'en']);

        $this->get('/halaman-yang-tidak-ada')->assertNotFound()->assertSee('Page not found');
        $this->get(route('public.articles.show', $draft))->assertNotFound()->assertSee('Page not found');
    }

    public function test_flash_messages_and_numbers_follow_chosen_language(): void
    {
        $user = User::factory()->pembaca()->create();
        $article = Article::factory()->published()->create();
        \DB::table('articles')->where('id', $article->id)->update(['view_count' => 1234]);

        $this->actingAs($user)->post(route('locale.update'), ['locale' => 'en']);

        $this->post(route('bookmarks.toggle', $article))->assertSessionHas('success', 'Article saved to Read Later.');

        // Membuka artikel ikut menambah 1 kunjungan, jadi yang diuji pemisah ribuan & kata, bukan angka persis.
        $this->assertMatchesRegularExpression('/1,23[45] views/', $this->get(route('public.articles.show', $article))->getContent());

        $this->post(route('locale.update'), ['locale' => 'id']);
        $this->assertMatchesRegularExpression('/1\.23[45] kali dilihat/', $this->get(route('public.articles.show', $article))->getContent());
    }

    public function test_every_text_has_an_english_translation(): void
    {
        $english = json_decode(File::get(lang_path('en.json')), true);

        // Semua view + PHP yang membuat teks untuk pengguna (controller, Form Request, bootstrap/app.php).
        $files = collect(File::allFiles(resource_path('views')))
            ->merge(File::allFiles(app_path('Http')))
            ->map(fn ($file) => (string) $file)
            ->push(base_path('bootstrap/app.php'));

        $indonesian = json_decode(File::get(lang_path('id.json')), true);

        $missing = [];
        foreach ($files as $file) {
            preg_match_all("/(__|trans_choice)\(\s*'((?:[^'\\\\]|\\\\.)*)'/", File::get($file), $matches, PREG_SET_ORDER);
            foreach ($matches as [, $function, $key]) {
                $key = str_replace("\\'", "'", $key);
                // Lewati kunci file PHP bawaan (auth.failed, pagination.next, ...).
                if (preg_match('/^[a-z]+\.[a-z_.]+$/', $key)) {
                    continue;
                }
                if (! array_key_exists($key, $english)) {
                    $missing[] = basename($file).': '.$key;
                }
                // trans_choice() memakai bahasa cadangan (en) bila kuncinya tidak ada di bahasa aktif, jadi kunci
                // Indonesia harus terdaftar juga di lang/id.json — kalau tidak, halaman Indonesia menampilkan Inggris.
                if ($function === 'trans_choice' && ! array_key_exists($key, $indonesian)) {
                    $missing[] = basename($file).' (id.json): '.$key;
                }
            }
        }

        // Templat pesan notifikasi (return '...' di message()) diterjemahkan saat tampil.
        foreach (File::files(app_path('Notifications')) as $file) {
            if (preg_match("/function message\\(\\): string\\s*\\{\\s*return '((?:[^'\\\\]|\\\\.)*)';/", File::get($file), $match)
                && ! array_key_exists($match[1], $english)) {
                $missing[] = $file->getFilename().': '.$match[1];
            }
        }

        $this->assertSame([], $missing, 'Teks tanpa terjemahan di lang/en.json / lang/id.json');
    }
}
