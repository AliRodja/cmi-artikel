<?php

namespace App\Policies;

use App\Models\User;

/*
 * Halaman Kelola Pengguna sudah dijaga middleware "admin". Policy ini menambah aturan
 * untuk akun sendiri: admin tidak bisa mengubah perannya sendiri atau menghapus akunnya
 * sendiri. Akibatnya selalu tersisa minimal satu admin, sehingga situs tidak terkunci.
 */
class UserPolicy
{
    public function changeRole(User $user, User $target): bool
    {
        return $user->isAdmin() && $user->isNot($target);
    }

    public function delete(User $user, User $target): bool
    {
        return $user->isAdmin() && $user->isNot($target);
    }
}
