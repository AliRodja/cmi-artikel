<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_response_has_security_headers_including_404(): void
    {
        foreach ([$this->get(route('home')), $this->get('/halaman-yang-tidak-ada')] as $response) {
            $response->assertHeader('X-Frame-Options', 'SAMEORIGIN')
                ->assertHeader('X-Content-Type-Options', 'nosniff')
                ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
                ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');
        }
    }

    public function test_hsts_is_only_sent_over_https(): void
    {
        $this->get('http://localhost/')->assertHeaderMissing('Strict-Transport-Security');
        $this->get('https://localhost/')->assertHeader('Strict-Transport-Security', 'max-age=31536000');
    }

    public function test_session_on_other_device_is_logged_out_after_password_changes(): void
    {
        $user = User::factory()->pembaca()->create();

        $this->actingAs($user)->get(route('profile.edit'))->assertOk();

        // Password diganti di tempat lain (perangkat lain, reset lewat email, atau oleh admin).
        $user->forceFill(['password' => 'password-baru-123'])->save();

        $this->get(route('profile.edit'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_user_who_changes_own_password_stays_logged_in(): void
    {
        $user = User::factory()->pembaca()->create();

        $this->actingAs($user)->get(route('profile.edit'))->assertOk();

        $this->put(route('profile.password'), [
            'current_password' => 'password',
            'password' => 'password-baru-123',
            'password_confirmation' => 'password-baru-123',
        ])->assertSessionHasNoErrors();

        $this->get(route('profile.edit'))->assertOk();
        $this->assertAuthenticatedAs($user);
    }

    public function test_changing_only_name_does_not_log_out(): void
    {
        $user = User::factory()->pembaca()->create();

        $this->actingAs($user)->get(route('profile.edit'))->assertOk();
        $user->update(['name' => 'Nama Baru']);

        $this->get(route('profile.edit'))->assertOk();
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_limit_counts_per_email_so_a_shared_office_ip_is_not_locked(): void
    {
        User::factory()->create(['email' => 'dokter@cmi.test']);
        User::factory()->create(['email' => 'perawat@cmi.test']);

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => 'dokter@cmi.test', 'password' => 'salah'])->assertStatus(302);
        }
        $this->post('/login', ['email' => 'dokter@cmi.test', 'password' => 'salah'])->assertStatus(429);

        // Rekan di Wi-Fi kantor yang sama (IP sama) tetap bisa masuk.
        $this->post('/login', ['email' => 'perawat@cmi.test', 'password' => 'password'])->assertRedirect(route('dashboard'));
    }

    public function test_register_reset_and_password_change_are_rate_limited(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->post('/register', [])->assertStatus(302);
            $this->post('/reset-password', [])->assertStatus(302);
        }
        $this->post('/register', [])->assertStatus(429);
        $this->post('/reset-password', [])->assertStatus(429);

        $this->actingAs(User::factory()->pembaca()->create());
        for ($i = 0; $i < 5; $i++) {
            $this->put(route('profile.password'), ['current_password' => 'tebakan-'.$i])->assertStatus(302);
        }
        $this->put(route('profile.password'), ['current_password' => 'tebakan-lagi'])->assertStatus(429);
    }

    public function test_write_actions_are_limited_per_account_but_page_views_are_not(): void
    {
        $article = Article::factory()->published()->create();
        $this->actingAs(User::factory()->pembaca()->create());

        for ($i = 0; $i < 40; $i++) {
            $this->get(route('bookmarks.index'))->assertOk();
        }
        for ($i = 0; $i < 30; $i++) {
            $this->post(route('bookmarks.toggle', $article))->assertStatus(302);
        }
        $this->post(route('bookmarks.toggle', $article))->assertStatus(429);
    }

    public function test_each_limit_has_its_own_counter_so_searching_does_not_block_commenting(): void
    {
        $article = Article::factory()->published()->create();
        $this->actingAs(User::factory()->pembaca()->create());

        // Tanpa nama penghitung, 15 kali live search (batas 60) ikut menghabiskan jatah komentar (batas 10).
        for ($i = 0; $i < 15; $i++) {
            $this->withHeader('X-Requested-With', 'XMLHttpRequest')->get(route('articles.live-search', ['q' => 'jantung']))->assertOk();
        }

        $this->post(route('comments.store', $article), ['body' => 'Terima kasih, sangat membantu.'])
            ->assertStatus(302)
            ->assertSessionHasNoErrors();
    }

    public function test_uploaded_photo_loses_exif_metadata_and_keeps_its_orientation(): void
    {
        Storage::fake('public');

        // JPEG 20×10 dengan EXIF: Orientation = 6 (dipotret miring, tampil tegak setelah diputar 90°) + penanda "GPS".
        $gd = imagecreatetruecolor(20, 10);
        ob_start();
        imagejpeg($gd);
        $jpeg = (string) ob_get_clean();
        $tiff = "II*\0".pack('V', 8).pack('v', 1).pack('vvV', 0x0112, 3, 1).pack('v', 6)."\0\0".pack('V', 0).'GPS-RAHASIA-123';
        $exif = "Exif\0\0".$tiff;
        $photo = substr($jpeg, 0, 2)."\xFF\xE1".pack('n', strlen($exif) + 2).$exif.substr($jpeg, 2);
        $this->assertSame(6, exif_read_data('data://image/jpeg;base64,'.base64_encode($photo))['Orientation']);

        $this->actingAs(User::factory()->create())->post(route('articles.store'), [
            'title' => 'Foto dari ponsel',
            'content' => '<div>Isi artikel.</div>',
            'status' => Article::STATUS_DRAFT,
            'image' => UploadedFile::fake()->createWithContent('foto.jpg', $photo),
        ])->assertSessionHasNoErrors();

        $stored = Storage::disk('public')->get(Article::firstOrFail()->image);
        $this->assertStringNotContainsString('GPS-RAHASIA', $stored);
        $this->assertStringNotContainsString('Exif', $stored);
        [$width, $height] = getimagesizefromstring($stored);
        $this->assertSame([10, 20], [$width, $height]);
    }

    public function test_search_treats_percent_and_underscore_as_plain_text(): void
    {
        Article::factory()->published()->create(['title' => 'Diskon 50% Cek Jantung', 'content' => 'Isi satu.']);
        Article::factory()->published()->create(['title' => 'Cek Jantung 500 Pasien', 'content' => 'Isi dua.']);
        Article::factory()->published()->create(['title' => 'Kode a_b Tekanan Darah', 'content' => 'Isi tiga.']);
        Article::factory()->published()->create(['title' => 'Kode axb Tekanan Darah', 'content' => 'Isi empat.']);

        $this->get(route('home', ['search' => '50%']))->assertSee('Diskon 50% Cek Jantung')->assertDontSee('Cek Jantung 500 Pasien');
        $this->get(route('home', ['search' => 'a_b']))->assertSee('Kode a_b Tekanan Darah')->assertDontSee('Kode axb Tekanan Darah');
        $this->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->get(route('articles.live-search', ['q' => '50%']))
            ->assertOk()->assertSee('Cek Jantung')->assertDontSee('500 Pasien');
    }

    public function test_error_page_back_button_never_points_to_another_site(): void
    {
        User::factory()->create(['email' => 'dokter@cmi.test']);
        $this->withHeader('referer', 'https://situs-jahat.test/jebakan');

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => 'dokter@cmi.test', 'password' => 'salah']);
        }

        $this->post('/login', ['email' => 'dokter@cmi.test', 'password' => 'salah'])
            ->assertStatus(429)
            ->assertDontSee('situs-jahat.test')
            ->assertSee('href="'.route('home').'"', false);
    }

    public function test_unused_local_storage_route_is_disabled(): void
    {
        $this->assertFalse(Route::has('storage.local'));
        $this->assertFalse(Route::has('storage.local.upload'));
    }
}
