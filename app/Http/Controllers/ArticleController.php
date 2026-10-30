<?php

namespace App\Http\Controllers;

use App\Http\Requests\ArticleRequest;
use App\Models\Article;
use App\Models\User;
use App\Support\ArticleNotifier;
use App\Support\ImageUpload;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
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

    public function create(Request $request): View
    {
        return view('articles.create', ['reviewers' => $this->reviewersFor($request->user())]);
    }

    public function store(ArticleRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['image', 'remove_image']);

        if ($request->hasFile('image')) {
            $data['image'] = ImageUpload::store($request->file('image'), 'articles');
        }

        $article = $request->user()->articles()->create($data);

        if ($article->isInReview()) {
            ArticleNotifier::submitted($article);
        } elseif ($article->isPublished()) {
            ArticleNotifier::published($article, $request->user());
        }

        return redirect()->route('articles.show', $article)->with('success', $article->isInReview()
            ? __('Artikel berhasil dibuat dan diajukan ke admin untuk direview.')
            : __('Artikel berhasil dibuat.'));
    }

    public function show(Request $request, Article $article): View
    {
        Gate::authorize('view', $article);

        // Statistik kunjungan dari riwayat article_views.
        $weeklyViews = $article->views()->where('viewed_at', '>=', now()->subDays(7))->count();
        $recentViews = $article->views()->with('user:id,name')->latest('viewed_at')->take(5)->get();

        $article->load(['user:id,name,profession', 'medicalReviewer:id,name,profession'])->loadCount('comments');
        $reviewers = $this->reviewersFor($request->user());

        return view('articles.show', compact('article', 'weeklyViews', 'recentViews', 'reviewers'));
    }

    public function edit(Request $request, Article $article): View
    {
        Gate::authorize('update', $article);

        return view('articles.edit', ['article' => $article, 'reviewers' => $this->reviewersFor($request->user())]);
    }

    public function update(ArticleRequest $request, Article $article): RedirectResponse
    {
        Gate::authorize('update', $article);

        $data = $request->safe()->except(['image', 'remove_image']);

        if ($request->hasFile('image')) {
            $this->deleteImage($article);
            $data['image'] = ImageUpload::store($request->file('image'), 'articles');
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
            ? __('Artikel berhasil diperbarui dan diajukan ke admin untuk direview.')
            : __('Artikel berhasil diperbarui.'));
    }

    public function destroy(Request $request, Article $article): RedirectResponse
    {
        Gate::authorize('delete', $article);

        $this->deleteImage($article);
        $article->delete();

        // Admin yang menghapus artikel orang lain dikembalikan ke halaman review.
        $redirectRoute = $article->user_id === $request->user()->id ? 'articles.index' : 'review.index';

        return redirect()->route($redirectRoute)->with('success', __('Artikel berhasil dihapus.'));
    }

    private function deleteImage(Article $article): void
    {
        if ($article->image) {
            Storage::disk('public')->delete($article->image);
        }
    }

    /** Pilihan "peninjau medis" (penulis & admin) — hanya admin yang boleh menentukannya. */
    private function reviewersFor(User $user): Collection
    {
        return $user->isAdmin() ? User::query()->writers()->get(['id', 'name', 'profession']) : collect();
    }
}
