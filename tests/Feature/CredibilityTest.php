<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Penanda kredibilitas & kemudahan bagi pembaca usia matang: profesi penulis, peninjau medis, sumber, info klinik. */
class CredibilityTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Mengenal Hipertensi', 'content' => 'Isi.', 'status' => Article::STATUS_DRAFT,
        ], $overrides);
    }

    public function test_article_shows_author_profession_medical_reviewer_and_sources(): void
    {
        $author = User::factory()->create(['name' => 'Rina', 'profession' => 'Ahli Gizi']);
        $doctor = User::factory()->create(['name' => 'dr. Andi', 'profession' => 'Dokter Spesialis Jantung (Sp.JP)']);
        $article = Article::factory()->for($author)->published()->create([
            'medical_reviewer_id' => $doctor->id,
            'sources' => "Kemenkes RI, Pedoman Hipertensi https://kemkes.go.id/hipertensi\n\nBuku Ajar Kardiologi\nBahaya <script>alert(1)</script> javascript:alert(1)",
        ]);

        $this->get(route('public.articles.show', $article))
            ->assertSee('Ahli Gizi')
            ->assertSee('Ditinjau secara medis oleh dr. Andi')
            ->assertSee('Dokter Spesialis Jantung (Sp.JP)')
            ->assertSeeInOrder(['Sumber rujukan', 'Kemenkes RI, Pedoman Hipertensi', 'kemkes.go.id', 'Buku Ajar Kardiologi'])
            ->assertSee('href="https://kemkes.go.id/hipertensi"', false)
            // Teks sumber di-escape, dan hanya http(s) yang dijadikan link.
            ->assertSee('&lt;script&gt;', false)
            ->assertDontSee('href="javascript:', false);
    }

    public function test_only_admin_can_set_medical_reviewer(): void
    {
        $doctor = User::factory()->create(['profession' => 'Dokter Umum']);
        $penulis = User::factory()->create();

        $this->actingAs($penulis)
            ->post(route('articles.store'), $this->payload(['medical_reviewer_id' => $doctor->id]))
            ->assertSessionHasErrors('medical_reviewer_id');
        $this->assertDatabaseCount('articles', 0);

        $this->actingAs(User::factory()->admin()->create())
            ->post(route('articles.store'), $this->payload(['medical_reviewer_id' => $doctor->id]))
            ->assertSessionHasNoErrors();
        $this->assertSame($doctor->id, Article::firstOrFail()->medical_reviewer_id);
    }

    public function test_reviewer_must_be_writer_and_can_be_chosen_when_publishing(): void
    {
        $admin = User::factory()->admin()->create();
        $article = Article::factory()->inReview()->create();
        $pembaca = User::factory()->pembaca()->create();
        $doctor = User::factory()->create(['profession' => 'Dokter Umum']);

        $this->actingAs($admin)
            ->patch(route('review.publish', $article), ['medical_reviewer_id' => $pembaca->id])
            ->assertSessionHasErrors('medical_reviewer_id');
        $this->assertTrue($article->fresh()->isInReview());

        $this->patch(route('review.publish', $article), ['medical_reviewer_id' => $doctor->id]);
        $article->refresh();
        $this->assertTrue($article->isPublished());
        $this->assertSame($doctor->id, $article->medical_reviewer_id);
    }

    public function test_user_can_fill_own_profession(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->patch(route('profile.update'), [
            'name' => $user->name, 'email' => $user->email, 'profession' => 'Perawat',
        ])->assertSessionHasNoErrors();

        $this->assertSame('Perawat', $user->fresh()->profession);
    }

    public function test_footer_shows_only_filled_clinic_info_and_whatsapp(): void
    {
        config([
            'clinic.address' => 'Jl. Contoh No. 1, Bandung',
            'clinic.hours' => null,
            'clinic.maps_url' => null,
            'clinic.phone' => '(022) 253 1000',
            'clinic.phone_international' => '+62222531000',
            'clinic.whatsapp' => '0811 9161 166',
            'clinic.whatsapp_url' => 'https://wa.me/628119161166',
        ]);

        $this->get(route('home'))
            ->assertSee('Hubungi Kami')
            ->assertSee('Jl. Contoh No. 1, Bandung')
            ->assertSee('href="tel:+62222531000"', false)
            ->assertSee('(022) 253 1000')
            ->assertSee('href="https://wa.me/628119161166"', false)
            ->assertSee('0811 9161 166')
            ->assertDontSee('Lihat di peta');

        config(['clinic.phone' => null, 'clinic.whatsapp' => null]);

        $this->get(route('home'))
            ->assertDontSee('Telepon:')
            ->assertDontSee('WhatsApp:');
    }

    public function test_clinic_contact_numbers_are_converted_to_international_format(): void
    {
        // env() membaca $_SERVER/$_ENV lebih dulu (diisi dari .env), jadi ketiganya ditimpa lalu dikembalikan.
        $values = [
            'CLINIC_ADDRESS' => 'Jl. Tubagus Ismail VII, Bandung',
            'CLINIC_PHONE' => '(022) 253 1000',
            'CLINIC_WHATSAPP' => '0811 9161 166',
            'CLINIC_MAPS_URL' => '',
        ];
        $original = array_map(fn ($key) => [$_SERVER[$key] ?? null, $_ENV[$key] ?? null, getenv($key)], array_combine(array_keys($values), array_keys($values)));

        foreach ($values as $key => $value) {
            $_SERVER[$key] = $_ENV[$key] = $value;
            putenv("{$key}={$value}");
        }

        try {
            $clinic = require config_path('clinic.php');
        } finally {
            foreach ($original as $key => [$server, $env, $putenv]) {
                if ($server === null) {
                    unset($_SERVER[$key]);
                } else {
                    $_SERVER[$key] = $server;
                }
                if ($env === null) {
                    unset($_ENV[$key]);
                } else {
                    $_ENV[$key] = $env;
                }
                putenv($putenv === false ? $key : "{$key}={$putenv}");
            }
        }

        $this->assertSame('+62222531000', $clinic['phone_international']);
        $this->assertSame('https://wa.me/628119161166', $clinic['whatsapp_url']);
        $this->assertSame('https://www.google.com/maps/search/?api=1&query=Jl.+Tubagus+Ismail+VII%2C+Bandung', $clinic['maps_url']);
        // Nomor tombol Konsultasi tidak ikut berubah.
        $this->assertStringContainsString('phone=6282121590000', $clinic['whatsapp_consultation_url']);
    }

    public function test_footer_links_to_official_cmi_social_media(): void
    {
        $response = $this->get(route('home'))->assertSee('Ikuti Kami');

        // URL persis seperti di footer cmihospital.com.
        foreach ([
            'https://www.facebook.com/cmihospitals',
            'https://www.instagram.com/cmihospitals/',
            'https://twitter.com/cmihospitals/',
            'https://www.tiktok.com/@cmihospitals',
            'https://www.youtube.com/c/CMIhospital-com',
        ] as $url) {
            $response->assertSee('href="'.$url.'"', false);
        }

        // Ikon tanpa teks tetap punya nama untuk pembaca layar, dan tab baru tidak bisa mengakses halaman kita.
        $response->assertSee(config('app.name').' di Instagram')
            ->assertSee('target="_blank" rel="noopener noreferrer" title="TikTok"', false);
    }

    public function test_icon_buttons_have_visible_text(): void
    {
        $article = Article::factory()->published()->create();

        $this->get(route('home'))->assertSee('<span class="hidden text-sm font-semibold sm:inline">Cari</span>', false);

        $this->get(route('public.articles.show', $article))
            ->assertSee('<span class="">WhatsApp</span>', false)
            ->assertSee('<span class="" data-copy-label>Salin tautan</span>', false);
    }
}
