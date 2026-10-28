<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Support\ArticleViewCounter;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicArticleController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        // ?jenis=jantung → hanya artikel jenis itu; nilai yang tidak dikenal diabaikan.
        $category = array_key_exists((string) $request->query('jenis'), Article::CATEGORIES)
            ? (string) $request->query('jenis')
            : null;

        $articles = Article::query()
            ->published()
            ->with('user:id,name')
            ->when($category, fn ($query) => $query->ofCategory($category))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('content', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(9)
            ->withQueryString();

        return view('public.index', compact('articles', 'search', 'category'));
    }

    public function show(Request $request, Article $article): View
    {
        abort_unless($article->isPublished(), 404);

        ArticleViewCounter::record($article, $request);

        $article->load([
            'user:id,name',
            // Komentar terlama di atas (urutan percakapan), sekaligus nama & peran penulisnya.
            'comments' => fn ($query) => $query->with('user:id,name,role')->oldest(),
        ]);

        $isBookmarked = (bool) $request->user()?->hasBookmarked($article);

        // Sidebar kanan: daftar artikel terbaru lainnya + jumlah artikel per jenis.
        $latestArticles = Article::query()
            ->published()
            ->whereKeyNot($article->getKey())
            ->latest()
            ->take(5)
            ->get(['id', 'title', 'slug', 'category', 'image', 'created_at']);

        // Di bawah artikel: artikel lain dengan jenis yang sama...
        $relatedArticles = $article->category
            ? Article::query()
                ->published()
                ->ofCategory($article->category)
                ->whereKeyNot($article->getKey())
                ->with('user:id,name')
                ->latest()
                ->take(3)
                ->get()
            : collect();

        // ...dan artikel yang paling sering dilihat, setelah melewati batas minimal kunjungan.
        $popularArticles = Article::query()
            ->published()
            ->whereKeyNot($article->getKey())
            ->where('view_count', '>=', config('clinic.popular_min_views'))
            ->orderByDesc('view_count')
            ->latest()
            ->take(6)
            ->get(['id', 'title', 'slug', 'category', 'image', 'view_count', 'created_at']);

        $categoryCounts = Article::query()
            ->published()
            ->whereNotNull('category')
            ->selectRaw('category, COUNT(*) as total')
            ->groupBy('category')
            ->pluck('total', 'category');

        return view('public.show', compact(
            'article', 'latestArticles', 'relatedArticles', 'popularArticles', 'categoryCounts', 'isBookmarked',
        ));
    }
}
