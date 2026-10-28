<?php

namespace App\Providers;

use App\Models\Article;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
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
