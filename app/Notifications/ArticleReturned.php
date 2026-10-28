<?php

namespace App\Notifications;

use Illuminate\Support\Str;

/** Untuk penulis: artikelnya dikembalikan admin dengan catatan. */
class ArticleReturned extends ArticleNotification
{
    protected function message(): string
    {
        return 'Artikel Anda dikembalikan admin: "'.Str::limit((string) $this->article->review_note, 80).'"';
    }

    protected function url(): string
    {
        return route('articles.show', $this->article, absolute: false);
    }

    protected function icon(): string
    {
        return 'exclamation-triangle';
    }
}
