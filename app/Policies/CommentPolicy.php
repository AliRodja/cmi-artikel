<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;

class CommentPolicy
{
    // Komentar boleh dihapus pemiliknya, atau admin (moderasi).
    public function delete(User $user, Comment $comment): bool
    {
        return $user->isAdmin() || $user->id === $comment->user_id;
    }
}
