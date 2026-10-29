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
        config(['clinic.address' => 'Jl. Contoh No. 1, Bandung', 'clinic.hours' => null]);

        $this->get(route('home'))
            ->assertSee('Hubungi Kami')
            ->assertSee('Jl. Contoh No. 1, Bandung')
            ->assertSee('WhatsApp CS:')
            ->assertSee(config('clinic.whatsapp_display'))
            ->assertDontSee('Lihat di peta');
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
