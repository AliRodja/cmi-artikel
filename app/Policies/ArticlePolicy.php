<?php

namespace App\Policies;

use App\Models\Article;
use App\Models\User;

/*
 * Aturan per artikel. Admin mengelola semua artikel; penulis hanya artikel
 * miliknya, dan tidak bisa lagi mengubah/menghapus artikel yang sudah tayang
 * (konten publik rumah sakit hanya diubah lewat admin).
 */
class ArticlePolicy
{
    public function view(User $user, Article $article): bool
    {
        return $user->isAdmin() || $this->owns($user, $article);
    }

    public function update(User $user, Article $article): bool
    {
        return $user->isAdmin() || ($this->owns($user, $article) && ! $article->isPublished());
    }

    public function delete(User $user, Article $article): bool
    {
        return $user->isAdmin() || ($this->owns($user, $article) && ! $article->isPublished());
    }

    // Terbitkan / kembalikan ke penulis: hanya admin, dan hanya untuk artikel yang sedang diajukan.
    public function review(User $user, Article $article): bool
    {
        return $user->isAdmin() && $article->isInReview();
    }

    private function owns(User $user, Article $article): bool
    {
        return $user->id === $article->user_id;
    }
}
