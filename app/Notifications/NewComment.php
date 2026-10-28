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
        return $this->comment->user->name.' mengomentari artikel Anda.';
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
