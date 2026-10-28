<?php

namespace App\Http\Controllers;

use App\Http\Requests\ArticleRequest;
use App\Models\Article;
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
            ->when(in_array($status, ['0', '1'], true), fn ($query) => $query->where('status', (int) $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('articles.index', compact('articles', 'search', 'status'));
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

        return redirect()->route('articles.show', $article)->with('success', 'Artikel berhasil dibuat.');
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

        $article->update($data);

        return redirect()->route('articles.show', $article)->with('success', 'Artikel berhasil diperbarui.');
    }

    public function destroy(Article $article): RedirectResponse
    {
        Gate::authorize('delete', $article);

        $this->deleteImage($article);
        $article->delete();

        return redirect()->route('articles.index')->with('success', 'Artikel berhasil dihapus.');
    }

    private function deleteImage(Article $article): void
    {
        if ($article->image) {
            Storage::disk('public')->delete($article->image);
        }
    }
}
