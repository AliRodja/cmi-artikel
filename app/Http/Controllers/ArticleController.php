<?php

namespace App\Http\Controllers;

use App\Http\Requests\ArticleRequest;
use App\Models\Article;
use App\Support\ArticleNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $status = $request->query('status');

        $articles = $request->user()->articles()
            ->when($search !== '', fn ($query) => $query->where('title', 'like', "%{$search}%"))
            ->when(in_array($status, ['0', '1', '2'], true), fn ($query) => $query->where('status', (int) $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        // Jumlah per status untuk tab filter (Semua / Published / Draft).
        $statusCounts = $request->user()->articles()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('articles.index', compact('articles', 'search', 'status', 'statusCounts'));
    }

    public function create(): View
    {
        return view('articles.create');
    }

    public function store(ArticleRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['image', 'remove_image']);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('articles', 'public');
        }

        $article = $request->user()->articles()->create($data);

        if ($article->isInReview()) {
            ArticleNotifier::submitted($article);
        } elseif ($article->isPublished()) {
            ArticleNotifier::published($article, $request->user());
        }

        return redirect()->route('articles.show', $article)->with('success', $article->isInReview()
            ? 'Artikel berhasil dibuat dan diajukan ke admin untuk direview.'
            : 'Artikel berhasil dibuat.');
    }

    public function show(Article $article): View
    {
        Gate::authorize('view', $article);

        return view('articles.show', compact('article'));
    }

    public function edit(Article $article): View
    {
        Gate::authorize('update', $article);

        return view('articles.edit', compact('article'));
    }

    public function update(ArticleRequest $request, Article $article): RedirectResponse
    {
        Gate::authorize('update', $article);

        $data = $request->safe()->except(['image', 'remove_image']);

        if ($request->hasFile('image')) {
            $this->deleteImage($article);
            $data['image'] = $request->file('image')->store('articles', 'public');
        } elseif ($request->boolean('remove_image')) {
            $this->deleteImage($article);
            $data['image'] = null;
        }

        // Catatan admin hanya relevan selama artikel kembali berstatus Draft.
        if ((int) $data['status'] !== Article::STATUS_DRAFT) {
            $data['review_note'] = null;
        }

        $article->update($data);

        // Notifikasi hanya saat status BERUBAH, agar mengedit artikel yang sudah diajukan/tayang tidak mengirim ulang.
        if ($article->wasChanged('status')) {
            if ($article->isInReview()) {
                ArticleNotifier::submitted($article);
            } elseif ($article->isPublished()) {
                ArticleNotifier::published($article, $request->user());
            }
        }

        return redirect()->route('articles.show', $article)->with('success', $article->isInReview()
            ? 'Artikel berhasil diperbarui dan diajukan ke admin untuk direview.'
            : 'Artikel berhasil diperbarui.');
    }

    public function destroy(Request $request, Article $article): RedirectResponse
    {
        Gate::authorize('delete', $article);

        $this->deleteImage($article);
        $article->delete();

        // Admin yang menghapus artikel orang lain dikembalikan ke halaman review.
        $redirectRoute = $article->user_id === $request->user()->id ? 'articles.index' : 'review.index';

        return redirect()->route($redirectRoute)->with('success', 'Artikel berhasil dihapus.');
    }

    private function deleteImage(Article $article): void
    {
        if ($article->image) {
            Storage::disk('public')->delete($article->image);
        }
    }
}
