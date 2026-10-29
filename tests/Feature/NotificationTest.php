<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use App\Notifications\ArticlePublished;
use App\Notifications\ArticleReturned;
use App\Notifications\ArticleSubmittedForReview;
use App\Notifications\NewArticlePublished;
use App\Notifications\NewComment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $penulis;

    private User $pembaca;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->penulis = User::factory()->create();
        $this->pembaca = User::factory()->pembaca()->create();
    }

    private function payload(int $status): array
    {
        return ['title' => 'Cegah Stunting Sejak Dini', 'content' => 'Isi artikel.', 'status' => $status];
    }

    public function test_admin_is_notified_when_penulis_submits_article(): void
    {
        Notification::fake();

        $this->actingAs($this->penulis)->post(route('articles.store'), $this->payload(Article::STATUS_REVIEW));

        Notification::assertSentTo($this->admin, ArticleSubmittedForReview::class);
        Notification::assertNotSentTo([$this->penulis, $this->pembaca], ArticleSubmittedForReview::class);
    }

    public function test_saving_a_draft_or_editing_an_article_in_review_does_not_notify_again(): void
    {
        Notification::fake();
        $article = Article::factory()->for($this->penulis)->inReview()->create();

        $this->actingAs($this->penulis)->post(route('articles.store'), $this->payload(Article::STATUS_DRAFT));
        $this->put(route('articles.update', $article), $this->payload(Article::STATUS_REVIEW));

        Notification::assertNothingSent();
    }

    public function test_publishing_notifies_author_and_readers(): void
    {
        Notification::fake();
        $article = Article::factory()->for($this->penulis)->inReview()->create();

        $this->actingAs($this->admin)->patch(route('review.publish', $article));

        Notification::assertSentTo($this->penulis, ArticlePublished::class);
        Notification::assertSentTo($this->pembaca, NewArticlePublished::class);
        Notification::assertNotSentTo($this->admin, ArticlePublished::class);
    }

    public function test_admin_publishing_own_article_only_notifies_readers(): void
    {
        Notification::fake();

        $this->actingAs($this->admin)->post(route('articles.store'), $this->payload(Article::STATUS_PUBLISHED));

        Notification::assertSentTo($this->pembaca, NewArticlePublished::class);
        Notification::assertNotSentTo($this->admin, ArticlePublished::class);
    }

    public function test_author_is_notified_when_article_is_returned(): void
    {
        Notification::fake();
        $article = Article::factory()->for($this->penulis)->inReview()->create();

        $this->actingAs($this->admin)->patch(route('review.return', $article), ['review_note' => 'Tambah sumber.']);

        Notification::assertSentTo($this->penulis, ArticleReturned::class);
    }

    public function test_author_is_notified_of_new_comment_but_not_of_own_comment(): void
    {
        Notification::fake();
        $article = Article::factory()->for($this->penulis)->published()->create();

        $this->actingAs($this->pembaca)->post(route('comments.store', $article), ['body' => 'Bermanfaat!']);
        Notification::assertSentTo($this->penulis, NewComment::class);

        Notification::fake();
        $this->actingAs($this->penulis)->post(route('comments.store', $article), ['body' => 'Terima kasih.']);
        Notification::assertNothingSent();
    }

    public function test_notification_appears_in_bell_and_opening_marks_it_read(): void
    {
        $article = Article::factory()->for($this->penulis)->inReview()->create();
        $this->actingAs($this->admin)->patch(route('review.publish', $article));

        $notification = $this->pembaca->notifications()->firstOrFail();

        $this->actingAs($this->pembaca)
            ->get(route('home'))
            ->assertSee('Notifikasi (1 belum dibaca)')
            ->assertSee('Artikel baru telah terbit.');

        $this->get(route('notifications.open', $notification->id))
            ->assertRedirect(route('public.articles.show', $article, absolute: false));

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_user_cannot_open_someone_elses_notification(): void
    {
        $article = Article::factory()->for($this->penulis)->inReview()->create();
        $this->actingAs($this->admin)->patch(route('review.publish', $article));
        $notification = $this->pembaca->notifications()->firstOrFail();

        $this->actingAs($this->penulis)
            ->get(route('notifications.open', $notification->id))
            ->assertNotFound();

        $this->assertNull($notification->fresh()->read_at);
    }

    public function test_mark_all_as_read(): void
    {
        $articles = Article::factory(2)->for($this->penulis)->inReview()->create();
        foreach ($articles as $article) {
            $this->actingAs($this->admin)->patch(route('review.publish', $article));
        }
        $this->assertSame(2, $this->pembaca->unreadNotifications()->count());

        $this->actingAs($this->pembaca)->post(route('notifications.read-all'));

        $this->assertSame(0, $this->pembaca->unreadNotifications()->count());
    }
}
