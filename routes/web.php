<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\ArticleReportController;
use App\Http\Controllers\ArticleReviewController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\BookmarkController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PublicArticleController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicArticleController::class, 'index'])->name('home');
Route::get('/artikel/{article}', [PublicArticleController::class, 'show'])->name('public.articles.show');

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);

    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:5,1');

    Route::get('/forgot-password', [ForgotPasswordController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('password.email');

    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'store'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    // Fitur semua peran (termasuk pembaca): komentar, Baca Nanti, notifikasi.
    Route::post('/artikel/{article}/komentar', [CommentController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('comments.store');
    Route::delete('/komentar/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');

    Route::get('/baca-nanti', [BookmarkController::class, 'index'])->name('bookmarks.index');
    Route::post('/artikel/{article}/simpan', [BookmarkController::class, 'toggle'])->name('bookmarks.toggle');

    Route::get('/notifikasi', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifikasi/{id}/buka', [NotificationController::class, 'open'])->name('notifications.open');
    Route::post('/notifikasi/baca-semua', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
});

Route::middleware(['auth', 'writer'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::prefix('dashboard')->group(function () {
        Route::resource('articles', ArticleController::class);

        Route::middleware('admin')->group(function () {
            Route::get('laporan-artikel', ArticleReportController::class)->name('articles.report');

            Route::get('review', [ArticleReviewController::class, 'index'])->name('review.index');
            Route::patch('articles/{article}/publish', [ArticleReviewController::class, 'publish'])->name('review.publish');
            Route::patch('articles/{article}/return', [ArticleReviewController::class, 'sendBack'])->name('review.return');
        });
    });
});
