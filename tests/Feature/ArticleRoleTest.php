<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleRoleTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $penulis;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->penulis = User::factory()->create();
    }

    private function payload(int $status, array $overrides = []): array
    {
        return array_merge([
            'title' => 'Pencegahan Demam Berdarah',
            'content' => 'Isi artikel kesehatan.',
            'status' => $status,
        ], $overrides);
    }

    public function test_registered_user_is_always_pembaca_even_if_role_is_sent(): void
    {
        $this->post('/register', [
            'name' => 'Pendaftar',
            'email' => 'pendaftar@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => User::ROLE_ADMIN,
        ]);

        $this->assertSame(User::ROLE_PEMBACA, User::where('email', 'pendaftar@example.com')->value('role'));
    }

    public function test_penulis_cannot_publish_directly(): void
    {
        $this->actingAs($this->penulis)
            ->post(route('articles.store'), $this->payload(Article::STATUS_PUBLISHED))
            ->assertSessionHasErrors('status');

        $this->assertDatabaseCount('articles', 0);
    }

    public function test_penulis_can_submit_for_review_and_it_stays_hidden_from_public(): void
    {
        $this->actingAs($this->penulis)
            ->post(route('articles.store'), $this->payload(Article::STATUS_REVIEW))
            ->assertSessionHasNoErrors();

        $article = Article::firstOrFail();
        $this->assertTrue($article->isInReview());

        $this->get(route('home'))->assertDontSee($article->title);
        $this->get(route('public.articles.show', $article))->assertNotFound();
    }

    public function test_penulis_cannot_edit_or_delete_own_published_article(): void
    {
        $article = Article::factory()->for($this->penulis)->published()->create();

        $this->actingAs($this->penulis);

        $this->get(route('articles.edit', $article))->assertForbidden();
        $this->put(route('articles.update', $article), $this->payload(Article::STATUS_DRAFT))->assertForbidden();
        $this->delete(route('articles.destroy', $article))->assertForbidden();

        $this->assertTrue($article->fresh()->isPublished());
    }

    public function test_penulis_cannot_open_admin_pages_or_review_actions(): void
    {
        $article = Article::factory()->for($this->penulis)->inReview()->create();

        $this->actingAs($this->penulis);

        $this->get(route('review.index'))->assertForbidden();
        $this->get(route('articles.report'))->assertForbidden();
        $this->patch(route('review.publish', $article))->assertForbidden();
        $this->patch(route('review.return', $article), ['review_note' => 'x'])->assertForbidden();

        $this->assertTrue($article->fresh()->isInReview());
    }

    public function test_admin_can_manage_any_article(): void
    {
        $article = Article::factory()->for($this->penulis)->published()->create();

        $this->actingAs($this->admin);

        $this->get(route('articles.show', $article))->assertOk();
        $this->put(route('articles.update', $article), $this->payload(Article::STATUS_DRAFT, ['title' => 'Diturunkan Admin']))
            ->assertSessionHasNoErrors();

        $article->refresh();
        $this->assertSame('Diturunkan Admin', $article->title);
        $this->assertSame($this->penulis->id, $article->user_id, 'Penulis asli tidak berubah saat admin mengedit.');

        $this->delete(route('articles.destroy', $article))->assertRedirect(route('review.index'));
        $this->assertModelMissing($article);
    }

    public function test_admin_publishes_article_from_review_queue(): void
    {
        $article = Article::factory()->for($this->penulis)->inReview()->create(['review_note' => 'Catatan lama']);

        $this->actingAs($this->admin)
            ->get(route('review.index'))
            ->assertOk()
            ->assertSee($article->title);

        $this->patch(route('review.publish', $article))->assertRedirect(route('review.index'));

        $article->refresh();
        $this->assertTrue($article->isPublished());
        $this->assertNull($article->review_note);
        $this->get(route('public.articles.show', $article))->assertOk();
    }

    public function test_admin_returns_article_with_required_note(): void
    {
        $article = Article::factory()->for($this->penulis)->inReview()->create();

        $this->actingAs($this->admin)
            ->patch(route('review.return', $article), ['review_note' => ''])
            ->assertSessionHasErrors('review_note');
        $this->assertTrue($article->fresh()->isInReview());

        $this->patch(route('review.return', $article), ['review_note' => 'Tambahkan sumber rujukan.']);

        $article->refresh();
        $this->assertSame(Article::STATUS_DRAFT, $article->status);
        $this->assertSame('Tambahkan sumber rujukan.', $article->review_note);

        $this->actingAs($this->penulis)
            ->get(route('articles.show', $article))
            ->assertSee('Tambahkan sumber rujukan.');
    }

    public function test_resubmitting_clears_the_admin_note(): void
    {
        $article = Article::factory()->for($this->penulis)->draft()->create(['review_note' => 'Perbaiki judul.']);

        $this->actingAs($this->penulis)
            ->put(route('articles.update', $article), $this->payload(Article::STATUS_REVIEW, ['title' => 'Judul Diperbaiki']));

        $article->refresh();
        $this->assertTrue($article->isInReview());
        $this->assertNull($article->review_note);
    }

    public function test_review_actions_only_apply_to_articles_in_review(): void
    {
        $draft = Article::factory()->for($this->penulis)->draft()->create();

        $this->actingAs($this->admin)
            ->patch(route('review.publish', $draft))
            ->assertForbidden();

        $this->assertSame(Article::STATUS_DRAFT, $draft->fresh()->status);
    }

    public function test_role_command_promotes_user_to_admin(): void
    {
        $this->artisan('user:role', ['email' => $this->penulis->email, 'role' => 'admin'])->assertSuccessful();
        $this->assertTrue($this->penulis->fresh()->isAdmin());

        $this->artisan('user:role', ['email' => $this->penulis->email, 'role' => 'superuser'])->assertFailed();
        $this->artisan('user:role', ['email' => 'tidak-ada@example.com', 'role' => 'admin'])->assertFailed();
    }
}
