<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ArticleContentTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    private function storeArticle(string $content)
    {
        return $this->actingAs($this->user)->post(route('articles.store'), [
            'title' => 'Artikel Rich Text',
            'content' => $content,
            'status' => Article::STATUS_DRAFT,
        ]);
    }

    public function test_rich_text_formatting_is_kept(): void
    {
        $this->storeArticle('<h1>Subjudul</h1><div><strong>Tebal</strong> dan <em>miring</em></div><ul><li>Satu</li></ul>');

        $content = Article::firstOrFail()->content;

        $this->assertStringContainsString('<h1>Subjudul</h1>', $content);
        $this->assertStringContainsString('<strong>Tebal</strong>', $content);
        $this->assertStringContainsString('<li>Satu</li>', $content);
    }

    public function test_dangerous_html_is_removed_before_saving(): void
    {
        $this->storeArticle(
            '<div>Aman</div><script>alert(1)</script>'
            .'<div onclick="alert(2)">Klik</div>'
            .'<a href="javascript:alert(3)">Tautan jahat</a>'
            .'<iframe src="https://example.com"></iframe>'
        );

        $content = Article::firstOrFail()->content;

        $this->assertStringContainsString('Aman', $content);
        $this->assertStringNotContainsString('<script', $content);
        $this->assertStringNotContainsString('onclick', $content);
        $this->assertStringNotContainsString('javascript:', $content);
        $this->assertStringNotContainsString('<iframe', $content);
    }

    public function test_links_open_in_new_tab_safely(): void
    {
        $this->storeArticle('<div><a href="https://example.com">Tautan</a></div>');

        $content = Article::firstOrFail()->content;

        $this->assertStringContainsString('target="_blank"', $content);
        $this->assertStringContainsString('rel="noopener noreferrer"', $content);
    }

    public function test_html_inserted_without_eloquent_is_sanitized_when_displayed(): void
    {
        $article = Article::factory()->for($this->user)->published()->create();
        DB::table('articles')->where('id', $article->id)->update(['content' => '<div>Isi</div><script>alert(1)</script>']);

        $this->get(route('public.articles.show', $article))
            ->assertOk()
            ->assertSee('Isi')
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_empty_editor_content_fails_validation(): void
    {
        $this->storeArticle('<div><br></div>')->assertSessionHasErrors('content');

        $this->assertDatabaseCount('articles', 0);
    }

    public function test_plain_text_content_becomes_paragraphs(): void
    {
        $article = Article::factory()->for($this->user)->create([
            'content' => "Paragraf pertama.\n\nParagraf <kedua> & terakhir.",
        ]);

        $this->assertSame(
            '<p>Paragraf pertama.</p><p>Paragraf &lt;kedua&gt; &amp; terakhir.</p>',
            $article->content
        );
        $this->assertSame('Paragraf pertama. Paragraf <kedua> & terakhir.', $article->excerpt);
    }
}
