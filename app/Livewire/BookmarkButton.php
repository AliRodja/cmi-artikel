<?php

namespace App\Livewire;

use App\Models\Article;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Tombol Simpan / Tersimpan (Baca Nanti) interaktif tanpa muat ulang halaman.
 * Dipasang di rel aksi kiri dan di kartu penulis pada halaman detail artikel publik;
 * keduanya disinkronkan lewat event "bookmark-toggled".
 */
class BookmarkButton extends Component
{
    #[Locked]
    public Article $article;

    public bool $isBookmarked = false;

    public string $buttonClass = 'share-btn';

    public bool $withLabel = false;

    public function mount(Article $article, bool $isBookmarked = false, string $buttonClass = 'share-btn', bool $withLabel = false): void
    {
        $this->article = $article;
        $this->isBookmarked = $isBookmarked;
        $this->buttonClass = $buttonClass;
        $this->withLabel = $withLabel;
    }

    public function toggle(): void
    {
        if (! auth()->check()) {
            session()->flash('error', __('Artikel belum tersimpan. Silakan masuk terlebih dahulu.'));
            $this->redirectRoute('login');

            return;
        }

        abort_unless($this->article->isPublished(), 404);

        $result = auth()->user()->bookmarks()->toggle($this->article->getKey());
        $this->isBookmarked = ! empty($result['attached']);

        $this->dispatch('bookmark-toggled', articleId: $this->article->getKey(), isBookmarked: $this->isBookmarked);
    }

    #[On('bookmark-toggled')]
    public function syncBookmarkState(int $articleId, bool $isBookmarked): void
    {
        if ($this->article->getKey() === $articleId) {
            $this->isBookmarked = $isBookmarked;
        }
    }

    public function render()
    {
        return view('livewire.bookmark-button');
    }
}
