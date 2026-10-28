<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Closure;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Session\TokenMismatchException;
use Tests\TestCase;

class PopupMessageTest extends TestCase
{
    use RefreshDatabase;

    /** Pemeriksaan CSRF dimatikan saat test; di sini dipaksa gagal seperti token yang kedaluwarsa. */
    private function simulateExpiredCsrfToken(): void
    {
        // Laravel 13: middleware CSRF di grup "web" bernama PreventRequestForgery.
        $this->app->instance(PreventRequestForgery::class, new class($this->app, $this->app['encrypter']) extends PreventRequestForgery
        {
            public function handle($request, Closure $next)
            {
                throw new TokenMismatchException('CSRF token mismatch.');
            }
        });
    }

    public function test_logout_success_shows_success_popup(): void
    {
        $this->actingAs(User::factory()->create())
            ->followingRedirects()
            ->post(route('logout'))
            ->assertSee('data-toast', false)
            ->assertSee('Berhasil')
            ->assertSee('Anda berhasil keluar.');

        $this->assertGuest();
    }

    public function test_logout_with_expired_page_shows_failure_popup_instead_of_419(): void
    {
        $user = User::factory()->create();
        $this->simulateExpiredCsrfToken();

        $this->actingAs($user)
            ->from(route('home'))
            ->post(route('logout'))
            ->assertRedirect(route('home'))
            ->assertSessionHas('error', 'Anda belum keluar karena sesi halaman sudah kedaluwarsa. Silakan coba lagi.');

        $this->assertAuthenticatedAs($user);
    }

    public function test_saving_article_shows_success_popup(): void
    {
        $article = Article::factory()->published()->create();

        $this->actingAs(User::factory()->pembaca()->create())
            ->from(route('public.articles.show', $article))
            ->followingRedirects()
            ->post(route('bookmarks.toggle', $article))
            ->assertSee('Berhasil')
            ->assertSee('Artikel disimpan ke Baca Nanti.');
    }

    public function test_guest_saving_article_gets_failure_popup_and_returns_to_article_after_login(): void
    {
        $article = Article::factory()->published()->create();
        $pembaca = User::factory()->pembaca()->create();

        $this->from(route('public.articles.show', $article))
            ->post(route('bookmarks.toggle', $article))
            ->assertRedirect(route('login'))
            ->assertSessionHas('error', 'Artikel belum tersimpan. Silakan masuk terlebih dahulu.');

        $this->get(route('login'))->assertSee('Gagal')->assertSee('Silakan masuk terlebih dahulu.');

        $this->post('/login', ['email' => $pembaca->email, 'password' => 'password'])
            ->assertRedirect(route('public.articles.show', $article));
    }

    public function test_invalid_form_shows_general_failure_popup(): void
    {
        $this->actingAs(User::factory()->create())
            ->from(route('articles.create'))
            ->followingRedirects()
            ->post(route('articles.store'), ['title' => ''])
            ->assertSee('Gagal')
            ->assertSee('Periksa kembali isian yang ditandai merah, lalu coba lagi.');
    }

    public function test_page_without_message_has_no_popup(): void
    {
        $this->get(route('home'))->assertOk()->assertDontSee('data-toast', false);
    }
}
