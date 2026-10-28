<?php

namespace App\Support;

use App\Models\Article;
use App\Models\Comment;
use App\Models\User;
use App\Notifications\ArticlePublished;
use App\Notifications\ArticleReturned;
use App\Notifications\ArticleSubmittedForReview;
use App\Notifications\NewArticlePublished;
use App\Notifications\NewComment;
use Illuminate\Support\Facades\Notification;

/*
 * Satu tempat yang menentukan SIAPA menerima notifikasi untuk setiap kejadian.
 * Controller cukup memanggil, misalnya ArticleNotifier::published($article, $admin).
 * Notifikasi dikirim langsung (tidak lewat queue), jadi tidak butuh queue worker.
 */
class ArticleNotifier
{
    /** Penulis mengajukan artikel → semua admin. */
    public static function submitted(Article $article): void
    {
        Notification::send(User::withRole(User::ROLE_ADMIN)->get(), new ArticleSubmittedForReview($article));
    }

    /** Artikel tayang → penulisnya (bila bukan dia sendiri yang menerbitkan) + semua pembaca. */
    public static function published(Article $article, User $publisher): void
    {
        if ($article->user && $article->user_id !== $publisher->id) {
            $article->user->notify(new ArticlePublished($article));
        }

        // chunkById: pembaca diambil 500 per batch agar memori tetap kecil walau pembacanya ribuan.
        User::withRole(User::ROLE_PEMBACA)->chunkById(500, function ($readers) use ($article) {
            Notification::send($readers, new NewArticlePublished($article));
        });
    }

    /** Admin mengembalikan artikel → penulisnya. */
    public static function returned(Article $article): void
    {
        $article->user?->notify(new ArticleReturned($article));
    }

    /** Komentar baru → penulis artikel (kecuali ia mengomentari artikelnya sendiri). */
    public static function commented(Comment $comment): void
    {
        $author = $comment->article->user;

        if ($author && $author->id !== $comment->user_id) {
            $author->notify(new NewComment($comment));
        }
    }
}
