<?php

namespace App\Support;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Menghitung kunjungan halaman artikel publik: mencatat riwayat (article_views) lalu
 * menambah articles.view_count, yang dipakai untuk daftar "Artikel Terpopuler".
 */
class ArticleViewCounter
{
    /** @return bool true bila kunjungan ini dihitung. */
    public static function record(Article $article, Request $request): bool
    {
        $user = $request->user();

        // Penulis yang membuka artikelnya sendiri tidak menambah angka.
        if ($user && $user->id === $article->user_id) {
            return false;
        }

        // Satu pengunjung dihitung sekali per artikel, jadi refresh berkali-kali tidak menggelembungkan angka.
        // User login dikenali dari id-nya; tamu dari sesinya (di-hash, ID sesi asli tidak disimpan).
        $visitor = $user ? "user:{$user->id}" : 'guest:'.hash('sha256', $request->session()->getId());

        if ($article->views()->where('visitor', $visitor)->exists()) {
            return false;
        }

        DB::transaction(function () use ($article, $user, $visitor) {
            $article->views()->create(['user_id' => $user?->id, 'visitor' => $visitor]);

            // UPDATE articles SET view_count = view_count + 1: dihitung di database (aman bila dua pengunjung
            // datang bersamaan) dan lewat query builder agar updated_at artikel tidak ikut berubah.
            DB::table('articles')->where('id', $article->id)->increment('view_count');
        });

        // Samakan angka di model yang sedang ditampilkan tanpa query ulang.
        $article->view_count++;
        $article->syncOriginalAttribute('view_count');

        return true;
    }
}
