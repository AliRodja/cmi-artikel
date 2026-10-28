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
        ]);
    }
}
