<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        return view('dashboard', [
            'totalCount' => $user->articles()->count(),
            'publishedCount' => $user->articles()->published()->count(),
            'draftCount' => $user->articles()->draft()->count(),
            'reviewCount' => $user->articles()->inReview()->count(),
            'returnedCount' => $user->articles()->draft()->whereNotNull('review_note')->count(),
            // Antrean seluruh penulis, hanya dihitung untuk admin.
            'pendingReviewCount' => $user->isAdmin() ? Article::inReview()->count() : 0,
            'latestArticles' => $user->articles()->latest()->take(5)->get(),
            // Jumlah pembaca semua artikel milik user & 3 artikel tayangnya yang paling banyak dibaca.
            'totalViews' => (int) $user->articles()->sum('view_count'),
            'topArticles' => $user->articles()->published()->where('view_count', '>', 0)
                ->orderByDesc('view_count')->take(3)->get(['id', 'title', 'slug', 'image', 'view_count']),
        ]);
    }
}
