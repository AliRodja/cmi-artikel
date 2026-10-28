<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleReportTest extends TestCase
{
    use RefreshDatabase;

    private User $viewer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->viewer = User::factory()->create(['name' => 'Penilai']);
    }

    private function reportTitles(array $query = []): array
    {
        return $this->actingAs($this->viewer)
            ->get(route('articles.report', $query))
            ->assertOk()
            ->viewData('articles')
            ->pluck('title')
            ->all();
    }

    public function test_inner_join_excludes_articles_without_author(): void
    {
        $author = User::factory()->create();
        Article::factory()->for($author)->published()->create(['title' => 'Punya Penulis']);
        Article::factory()->published()->create(['user_id' => null, 'title' => 'Tanpa Penulis']);

        $titles = $this->reportTitles();

        $this->assertContains('Punya Penulis', $titles);
        $this->assertNotContains('Tanpa Penulis', $titles);
    }

    public function test_only_published_articles_are_shown(): void
    {
        $author = User::factory()->create();
        Article::factory()->for($author)->published()->create(['title' => 'Artikel Published']);
        Article::factory()->for($author)->draft()->create(['title' => 'Artikel Draft']);

        $this->assertSame(['Artikel Published'], $this->reportTitles());
    }

    public function test_row_contains_author_name_and_email_from_users_table(): void
    {
        $author = User::factory()->create(['name' => 'Siti Penulis', 'email' => 'siti@example.com']);
        $article = Article::factory()->for($author)->published()->create();

        $row = $this->actingAs($this->viewer)->get(route('articles.report'))->viewData('articles')->first();

        $this->assertSame($article->title, $row->title);
        $this->assertSame('Siti Penulis', $row->author_name);
        $this->assertSame('siti@example.com', $row->author_email);

        $this->actingAs($this->viewer)->get(route('articles.report'))
            ->assertSee('Siti Penulis')
            ->assertSee('siti@example.com');
    }

    public function test_articles_are_sorted_by_created_at_descending(): void
    {
        $author = User::factory()->create();
        Article::factory()->for($author)->published()->create(['title' => 'Paling Lama', 'created_at' => now()->subDays(10)]);
        Article::factory()->for($author)->published()->create(['title' => 'Paling Baru', 'created_at' => now()->subDay()]);
        Article::factory()->for($author)->published()->create(['title' => 'Tengah', 'created_at' => now()->subDays(5)]);

        $this->assertSame(['Paling Baru', 'Tengah', 'Paling Lama'], $this->reportTitles());
    }

    public function test_report_is_paginated_ten_per_page(): void
    {
        Article::factory(13)->for(User::factory())->published()->create();

        $page1 = $this->actingAs($this->viewer)->get(route('articles.report'))->viewData('articles');
        $this->assertCount(10, $page1->items());
        $this->assertSame(13, $page1->total());

        $page2 = $this->actingAs($this->viewer)->get(route('articles.report', ['page' => 2]))->viewData('articles');
        $this->assertCount(3, $page2->items());
    }

    public function test_search_filters_by_title_author_name_or_email(): void
    {
        $budi = User::factory()->create(['name' => 'Budi Santoso', 'email' => 'budi@example.com']);
        $ani = User::factory()->create(['name' => 'Ani Wijaya', 'email' => 'ani@kampus.ac.id']);
        Article::factory()->for($budi)->published()->create(['title' => 'Belajar Laravel']);
        Article::factory()->for($ani)->published()->create(['title' => 'Resep Nasi Goreng']);

        $this->assertSame(['Belajar Laravel'], $this->reportTitles(['search' => 'laravel']));
        $this->assertSame(['Resep Nasi Goreng'], $this->reportTitles(['search' => 'Ani']));
        $this->assertSame(['Resep Nasi Goreng'], $this->reportTitles(['search' => 'kampus']));
    }

    public function test_search_does_not_leak_draft_articles(): void
    {
        $budi = User::factory()->create(['name' => 'Budi']);
        Article::factory()->for($budi)->published()->create(['title' => 'Published Budi']);
        Article::factory()->for($budi)->draft()->create(['title' => 'Draft Budi']);

        $this->assertSame(['Published Budi'], $this->reportTitles(['search' => 'Budi']));
    }

    public function test_search_keyword_is_kept_in_pagination_links(): void
    {
        Article::factory(12)->for(User::factory()->create(['name' => 'Budi']))->published()->create();

        $paginator = $this->actingAs($this->viewer)
            ->get(route('articles.report', ['search' => 'Budi']))
            ->viewData('articles');

        $this->assertStringContainsString('search=Budi', $paginator->nextPageUrl());
    }

    public function test_guest_cannot_open_report(): void
    {
        $this->get(route('articles.report'))->assertRedirect(route('login'));
    }
}
