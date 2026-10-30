<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Comment;
use App\Support\ArticleNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CommentController extends Controller
{
    public function store(Request $request, Article $article): RedirectResponse
    {
        // Artikel Draft/Review tidak tampil di publik, jadi juga tidak bisa dikomentari.
        abort_unless($article->isPublished(), 404);

        $validated = $request->validate(
            ['body' => ['required', 'string', 'max:2000']],
            attributes: ['body' => 'komentar'],
        );

        // article_id & user_id diisi lewat relasi, tidak pernah dari input form.
        $comment = $article->comments()->make($validated);
        $comment->user()->associate($request->user());
        $comment->save();

        ArticleNotifier::commented($comment);

        return redirect(route('public.articles.show', $article).'#komentar')
            ->with('success', __('Komentar Anda sudah ditambahkan.'));
    }

    public function destroy(Comment $comment): RedirectResponse
    {
        Gate::authorize('delete', $comment);

        $comment->delete();

        return redirect(route('public.articles.show', $comment->article).'#komentar')
            ->with('success', __('Komentar dihapus.'));
    }
}
