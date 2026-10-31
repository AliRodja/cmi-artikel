<?php

namespace App\Livewire;

use App\Models\Article;
use App\Support\ArticleNotifier;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Kolom komentar artikel publik berbasis Livewire: tambah & hapus komentar
 * secara langsung tanpa memuat ulang halaman.
 */
class ArticleComments extends Component
{
    #[Locked]
    public Article $article;

    public string $body = '';

    public ?string $feedbackMessage = null;

    public function mount(Article $article): void
    {
        $this->article = $article;
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'body' => __('komentar'),
        ];
    }

    public function store(): void
    {
        abort_unless(auth()->check(), 403);
        abort_unless($this->article->isPublished(), 404);

        $validated = $this->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        // article_id & user_id diisi lewat relasi, tidak pernah dari input form.
        $comment = $this->article->comments()->make($validated);
        $comment->user()->associate(auth()->user());
        $comment->save();

        ArticleNotifier::commented($comment);

        $this->reset('body');
        $this->feedbackMessage = __('Komentar Anda sudah ditambahkan.');
    }

    public function destroy(int $commentId): void
    {
        $comment = $this->article->comments()->findOrFail($commentId);

        Gate::authorize('delete', $comment);

        $comment->delete();

        $this->feedbackMessage = __('Komentar dihapus.');
    }

    public function render(): View
    {
        $comments = $this->article->comments()
            ->with('user:id,name,role')
            ->oldest()
            ->get();

        return view('livewire.article-comments', [
            'comments' => $comments,
        ]);
    }
}
