<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReaderFeaturesTest extends TestCase
{
    use RefreshDatabase;

    private User $pembaca;

    private Article $article;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pembaca = User::factory()->pembaca()->create();
        $this->article = Article::factory()->published()->create();
    }

    public function test_pembaca_cannot_open_writer_area(): void
    {
        $this->actingAs($this->pembaca);

        $this->get(route('dashboard'))->assertForbidden();
        $this->get(route('articles.index'))->assertForbidden();
        $this->get(route('articles.create'))->assertForbidden();
        $this->post(route('articles.store'), [
            'title' => 'Coba', 'content' => 'Isi', 'status' => Article::STATUS_DRAFT,
        ])->assertForbidden();

        $this->assertDatabaseCount('articles', 1);
    }

    public function test_pembaca_is_sent_home_after_login(): void
    {
        $this->post('/login', ['email' => $this->pembaca->email, 'password' => 'password'])
            ->assertRedirect(route('home'));
    }

    public function test_logged_in_user_can_comment_on_published_article(): void
    {
        $this->actingAs($this->pembaca)
            ->post(route('comments.store', $this->article), ['body' => 'Terima kasih, sangat bermanfaat.'])
            ->assertRedirect(route('public.articles.show', $this->article).'#komentar');

        $comment = Comment::firstOrFail();
        $this->assertSame($this->pembaca->id, $comment->user_id, 'user_id diambil dari user login, bukan dari form.');

        $this->get(route('public.articles.show', $this->article))->assertSee('Terima kasih, sangat bermanfaat.');
    }

    public function test_comment_is_validated_escaped_and_needs_login(): void
    {
        $this->post(route('comments.store', $this->article), ['body' => 'Tamu'])->assertRedirect(route('login'));

        $this->actingAs($this->pembaca)
            ->post(route('comments.store', $this->article), ['body' => ''])
            ->assertSessionHasErrors('body');

        $this->post(route('comments.store', $this->article), ['body' => '<script>alert(1)</script>']);
        $this->get(route('public.articles.show', $this->article))
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('&lt;script&gt;', false);
    }

    public function test_cannot_comment_on_unpublished_article(): void
    {
        $draft = Article::factory()->draft()->create();

        $this->actingAs($this->pembaca)
            ->post(route('comments.store', $draft), ['body' => 'Halo'])
            ->assertNotFound();
    }

    public function test_only_owner_or_admin_can_delete_comment(): void
    {
        $comment = Comment::factory()->for($this->article)->for($this->pembaca)->create();
        $other = User::factory()->pembaca()->create();

        $this->actingAs($other)->delete(route('comments.destroy', $comment))->assertForbidden();
        $this->assertModelExists($comment);

        $this->actingAs(User::factory()->admin()->create())->delete(route('comments.destroy', $comment))->assertRedirect();
        $this->assertModelMissing($comment);

        $own = Comment::factory()->for($this->article)->for($this->pembaca)->create();
        $this->actingAs($this->pembaca)->delete(route('comments.destroy', $own))->assertRedirect();
        $this->assertModelMissing($own);
    }

    public function test_bookmark_toggle_and_read_later_list(): void
    {
        $this->actingAs($this->pembaca)->post(route('bookmarks.toggle', $this->article));
        $this->assertTrue($this->pembaca->hasBookmarked($this->article));

        $this->get(route('bookmarks.index'))->assertOk()->assertSee($this->article->title);

        $this->post(route('bookmarks.toggle', $this->article));
        $this->assertFalse($this->pembaca->hasBookmarked($this->article));
    }

    public function test_unpublished_article_disappears_from_read_later(): void
    {
        $this->pembaca->bookmarks()->attach($this->article);
        $this->article->update(['status' => Article::STATUS_DRAFT]);

        $this->actingAs($this->pembaca)
            ->get(route('bookmarks.index'))
            ->assertDontSee($this->article->title);
    }
}
