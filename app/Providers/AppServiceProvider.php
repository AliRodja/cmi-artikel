<?php

namespace App\Providers;

use Cloudinary\Cloudinary;
use App\Models\Article;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
        public function register(): void
    {
        // SDK Cloudinary dibuat lewat container (bukan "new" di ImageUpload) agar test bisa menggantinya dengan tiruan.
        $this->app->bind(Cloudinary::class, fn () => new Cloudinary((string) config('services.cloudinary.url')));
    }

    /**
     * Batas percobaan (dipakai di routes/web.php lewat middleware throttle:<nama>). Lewat batas → halaman 429.
     */
    private function configureRateLimiting(): void
    {
        // Masuk & Lupa Password: dihitung per email + IP, bukan per IP saja. Staf klinik yang memakai Wi-Fi kantor
        // yang sama (satu IP) tidak saling mengunci; batas per IP tetap ada agar satu orang tidak bisa mencoba
        // ratusan email berbeda.
        RateLimiter::for('auth-attempts', fn (Request $request) => [
            Limit::perMinute(5)->by(Str::lower((string) $request->input('email')).'|'.$request->ip()),
            Limit::perMinute(30)->by($request->ip()),
        ]);

        // Semua aksi simpan/ubah/hapus setelah login, per akun. Membuka halaman (GET) tidak dihitung.
        RateLimiter::for('writes', fn (Request $request) => $request->isMethodSafe()
            ? Limit::none()
            : Limit::perMinute(30)->by((string) ($request->user()?->id ?? $request->ip())));
    }

    public function boot(): void
    {
        $this->configureRateLimiting();

        // Data navbar di semua halaman: angka antrean Review (admin) dan lonceng notifikasi (semua yang login).
        View::composer('partials.navbar', function ($view) {
            $user = auth()->user();

            $view->with([
                'pendingReviewCount' => $user?->isAdmin() ? Article::inReview()->count() : 0,
                'unreadNotificationCount' => $user ? $user->unreadNotifications()->count() : 0,
                'latestNotifications' => $user ? $user->notifications()->take(5)->get() : collect(),
            ]);
        });
    }
}
