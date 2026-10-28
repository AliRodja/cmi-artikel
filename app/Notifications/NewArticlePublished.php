<?php

namespace App\Notifications;

/** Untuk pembaca: ada artikel baru yang tayang. */
class NewArticlePublished extends ArticleNotification
{
    protected function message(): string
    {
        return 'Artikel baru telah terbit.';
    }

    protected function url(): string
    {
        return route('public.articles.show', $this->article, absolute: false);
    }

    protected function icon(): string
    {
        return 'newspaper';
    }
}
