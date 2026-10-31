<?php

use App\Models\Article;
use App\Models\User;
use App\Support\ImageUpload;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
 * Mengubah peran: php artisan user:role email@contoh.com penulis   (admin / penulis / pembaca)
 * Tidak ada halaman untuk ini supaya peran tidak bisa diubah dari browser.
 */
Artisan::command('user:role {email} {role : admin, penulis, atau pembaca}', function (string $email, string $role) {
    if (! in_array($role, User::ROLES, true)) {
        $this->error('Peran harus "admin", "penulis", atau "pembaca".');

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
})->purpose('Ubah peran user (admin / penulis / pembaca)');


/*
 * Memindahkan gambar artikel lama (storage/app/public) ke Cloudinary. Backup database dulu!
 *   php artisan images:to-cloudinary --dry-run   (hanya menampilkan daftar, tidak mengubah apa pun)
 *   php artisan images:to-cloudinary
 * File lokal TIDAK dihapus (cadangan) dan updated_at artikel tidak berubah. Aman dijalankan ulang:
 * gambar yang sudah berawalan "cloudinary:" dilewati.
 */
Artisan::command('images:to-cloudinary {--dry-run : Tampilkan daftar tanpa mengunggah}', function () {
    if (blank(config('services.cloudinary.url'))) {
        $this->error('CLOUDINARY_URL belum diisi di .env.');

        return 1;
    }

    $articles = Article::whereNotNull('image')->where('image', 'not like', 'cloudinary:%')->get(['id', 'title', 'image']);

    if ($articles->isEmpty()) {
        $this->info('Tidak ada gambar lama. Semua gambar artikel sudah di Cloudinary.');

        return 0;
    }

    $moved = 0;

    foreach ($articles as $article) {
        if (! Storage::disk('public')->exists($article->image)) {
            $this->warn("Lewati #{$article->id} {$article->title}: file {$article->image} tidak ditemukan.");

            continue;
        }

        if ($this->option('dry-run')) {
            $this->line("Akan dipindah: #{$article->id} {$article->title} ({$article->image})");

            continue;
        }

        try {
            $image = ImageUpload::copyToCloudinary($article->image);
        } catch (ValidationException) {
            $this->error("Gagal mengunggah #{$article->id} {$article->title}. Cek internet & CLOUDINARY_URL, lalu jalankan ulang.");

            continue;
        }

        // Query builder, bukan $article->save(): updated_at (tanggal "diperbarui" & sitemap) tidak ikut berubah.
        DB::table('articles')->where('id', $article->id)->update(['image' => $image]);
        $this->info("Dipindah: #{$article->id} {$article->title} → {$image}");
        $moved++;
    }

    if (! $this->option('dry-run')) {
        $this->newLine();
        $this->info("Selesai: {$moved} dari {$articles->count()} gambar dipindah. File lama tetap ada di storage/app/public sebagai cadangan.");
    }
})->purpose('Pindahkan gambar artikel lama ke Cloudinary');
