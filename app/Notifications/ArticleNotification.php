<?php

namespace App\Notifications;

use App\Models\Article;
use Illuminate\Notifications\Notification;

/*
 * Dasar semua notifikasi artikel. Disimpan di tabel "notifications" (channel
 * database bawaan Laravel) lalu ditampilkan di lonceng navbar & halaman Notifikasi.
 * Setiap notifikasi cukup menentukan pesan, tujuan link, dan ikonnya.
 */
abstract class ArticleNotification extends Notification
{
    public function __construct(public Article $article) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'article_id' => $this->article->id,
            'title' => $this->article->title,
            'message' => $this->message(),
            // Path relatif (tanpa domain) agar link tetap benar walau APP_URL berubah.
            'url' => $this->url(),
            'icon' => $this->icon(),
        ];
    }

    abstract protected function message(): string;

    abstract protected function url(): string;

    abstract protected function icon(): string;
}
