<?php

namespace App\Notifications;

use App\Models\Comment;

/** Untuk penulis: ada komentar baru di artikelnya. */
class NewComment extends ArticleNotification
{
    public function __construct(public Comment $comment)
    {
        parent::__construct($comment->article);
    }

    protected function message(): string
    {
        return ':name mengomentari artikel Anda.';
    }

    protected function replace(): array
    {
        return ['name' => $this->comment->user->name];
    }

    protected function url(): string
    {
        return route('public.articles.show', $this->article, absolute: false).'#komentar';
    }

    protected function icon(): string
    {
        return 'comment';
    }
}
