<?php

namespace App\Notifications;

/** Untuk admin: penulis mengajukan artikel untuk direview. */
class ArticleSubmittedForReview extends ArticleNotification
{
    protected function message(): string
    {
        return ':name mengajukan artikel untuk direview.';
    }

    protected function replace(): array
    {
        return ['name' => $this->article->user?->name ?? 'Penulis'];
    }

    protected function url(): string
    {
        return route('articles.show', $this->article, absolute: false);
    }

    protected function icon(): string
    {
        return 'clock';
    }
}
