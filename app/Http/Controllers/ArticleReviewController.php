<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/*
 * Area admin (route dijaga middleware "admin"): antrean review dan semua artikel
 * dari semua penulis. Aksi per artikel tetap dicek lagi oleh ArticlePolicy@review.
 */
class ArticleReviewController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        // Tanpa filter, admin langsung melihat antrean "Menunggu Review".
        $status = $request->query('status', (string) Article::STATUS_REVIEW);

        $articles = Article::query()
            ->with('user:id,name,email')
            ->when($search !== '', fn ($query) => $query->where('title', 'like', "%{$search}%"))
            ->when(in_array($status, ['0', '1', '2'], true), fn ($query) => $query->where('status', (int) $status))
            ->latest('updated_at')
            ->paginate(10)
            ->withQueryString();

        $statusCounts = Article::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('review.index', compact('articles', 'search', 'status', 'statusCounts'));
    }

    public function publish(Article $article): RedirectResponse
    {
        Gate::authorize('review', $article);

        $article->update(['status' => Article::STATUS_PUBLISHED, 'review_note' => null]);

        return redirect()->route('review.index')
            ->with('success', 'Artikel "'.$article->title.'" sudah diterbitkan.');
    }

    public function sendBack(Request $request, Article $article): RedirectResponse
    {
        Gate::authorize('review', $article);

        $validated = $request->validate(
            ['review_note' => ['required', 'string', 'max:1000']],
            attributes: ['review_note' => 'catatan untuk penulis'],
        );

        $article->update(['status' => Article::STATUS_DRAFT, 'review_note' => $validated['review_note']]);

        return redirect()->route('review.index')
            ->with('success', 'Artikel "'.$article->title.'" dikembalikan ke penulis.');
    }
}
