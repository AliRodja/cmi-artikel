<?php

use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
 * Menunjuk admin: php artisan user:role email@contoh.com admin
 * Tidak ada halaman untuk ini supaya peran tidak bisa diubah dari browser.
 */
Artisan::command('user:role {email} {role : admin atau penulis}', function (string $email, string $role) {
    if (! in_array($role, [User::ROLE_ADMIN, User::ROLE_PENULIS], true)) {
        $this->error('Peran harus "admin" atau "penulis".');

        return 1;
    }

    $user = User::where('email', $email)->first();

    if (! $user) {
        $this->error("User dengan email {$email} tidak ditemukan.");

        return 1;
    }

    // forceFill karena "role" sengaja tidak ada di $fillable.
    $user->forceFill(['role' => $role])->save();
    $this->info("{$user->name} sekarang berperan sebagai {$user->role_label}.");
})->purpose('Ubah peran user (admin / penulis)');
