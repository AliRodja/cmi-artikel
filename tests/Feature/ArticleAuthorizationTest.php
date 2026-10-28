<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private User $otherUser;

    private Article $article;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create();
        $this->otherUser = User::factory()->create();
        $this->article = Article::factory()->for($this->owner)->published()->create();
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Judul Baru',
            'content' => 'Isi artikel baru.',
            'status' => Article::STATUS_PUBLISHED,
        ], $overrides);
    }

    public function test_owner_can_view_edit_update_and_delete_own_article(): void
    {
        $this->actingAs($this->owner);

        $this->get(route('articles.show', $this->article))->assertOk();
        $this->get(route('articles.edit', $this->article))->assertOk();

        $this->put(route('articles.update', $this->article), $this->validPayload(['title' => 'Judul Diedit']))
            ->assertRedirect(route('articles.show', $this->article));
        $this->assertSame('Judul Diedit', $this->article->fresh()->title);

        $this->delete(route('articles.destroy', $this->article))->assertRedirect(route('articles.index'));
        $this->assertModelMissing($this->article);
    }

    public function test_other_user_cannot_view_or_edit_article(): void
    {
        $this->actingAs($this->otherUser);

        $this->get(route('articles.show', $this->article))->assertForbidden();
        $this->get(route('articles.edit', $this->article))->assertForbidden();
    }

    public function test_other_user_cannot_update_article(): void
    {
        $originalTitle = $this->article->title;

        $this->actingAs($this->otherUser)
            ->put(route('articles.update', $this->article), $this->validPayload(['title' => 'Dibajak']))
            ->assertForbidden();

        $this->assertSame($originalTitle, $this->article->fresh()->title);
    }

    public function test_other_user_cannot_delete_article(): void
    {
        $this->actingAs($this->otherUser)
            ->delete(route('articles.destroy', $this->article))
            ->assertForbidden();

        $this->assertModelExists($this->article);
    }

    public function test_article_list_only_shows_own_articles(): void
    {
        $othersArticle = Article::factory()->for($this->otherUser)->create();

        $this->actingAs($this->owner)
            ->get(route('articles.index'))
            ->assertOk()
            ->assertSee($this->article->title)
            ->assertDontSee($othersArticle->title);
    }

    public function test_guest_cannot_access_article_management(): void
    {
        $this->get(route('articles.create'))->assertRedirect(route('login'));
        $this->post(route('articles.store'), $this->validPayload())->assertRedirect(route('login'));
        $this->put(route('articles.update', $this->article), $this->validPayload())->assertRedirect(route('login'));
        $this->delete(route('articles.destroy', $this->article))->assertRedirect(route('login'));

        $this->assertDatabaseCount('articles', 1);
    }

    public function test_user_id_comes_from_logged_in_user_not_from_form(): void
    {
        $this->actingAs($this->owner)
            ->post(route('articles.store'), $this->validPayload(['user_id' => $this->otherUser->id]));

        $created = Article::where('title', 'Judul Baru')->firstOrFail();
        $this->assertSame($this->owner->id, $created->user_id);
    }

    public function test_public_can_see_published_article_but_not_draft(): void
    {
        $draft = Article::factory()->for($this->owner)->draft()->create();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee($this->article->title)
            ->assertDontSee($draft->title);

        $this->get(route('public.articles.show', $this->article))->assertOk();
        $this->get(route('public.articles.show', $draft))->assertNotFound();
    }

    public function test_public_article_page_has_no_manage_buttons(): void
    {
        $this->get(route('public.articles.show', $this->article))
            ->assertOk()
            ->assertDontSee(route('articles.edit', $this->article))
            ->assertDontSee(route('articles.destroy', $this->article));
    }
}
