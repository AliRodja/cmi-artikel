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
        // Angka antrean di menu "Review" navbar, tampil di semua halaman untuk admin.
        View::composer('partials.navbar', function ($view) {
            $view->with('pendingReviewCount', auth()->user()?->isAdmin() ? Article::inReview()->count() : 0);
        });
    }
}
