<?php

namespace App\Notifications;

/** Untuk penulis: artikelnya sudah diterbitkan admin. */
class ArticlePublished extends ArticleNotification
{
    protected function message(): string
    {
        return 'Artikel Anda sudah diterbitkan dan tampil di situs.';
    }

    protected function url(): string
    {
        return route('public.articles.show', $this->article, absolute: false);
    }

    protected function icon(): string
    {
        return 'check-circle';
    }
}
