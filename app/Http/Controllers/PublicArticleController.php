<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicArticleController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));

        $articles = Article::query()
            ->published()
            ->with('user:id,name')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('content', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(9)
            ->withQueryString();

        return view('public.index', compact('articles', 'search'));
    }

    public function show(Request $request, Article $article): View
    {
        abort_unless($article->isPublished(), 404);

        $article->load([
            'user:id,name',
            // Komentar terlama di atas (urutan percakapan), sekaligus nama & peran penulisnya.
            'comments' => fn ($query) => $query->with('user:id,name,role')->oldest(),
        ]);

        $isBookmarked = (bool) $request->user()?->hasBookmarked($article);

        $relatedArticles = Article::query()
            ->published()
            ->whereKeyNot($article->getKey())
            ->with('user:id,name')
            ->latest()
            ->take(3)
            ->get();

        return view('public.show', compact('article', 'relatedArticles', 'isBookmarked'));
    }
}
