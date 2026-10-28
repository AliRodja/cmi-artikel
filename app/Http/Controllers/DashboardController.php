<?php

namespace App\Http\Controllers;

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
            'latestArticles' => $user->articles()->latest()->take(5)->get(),
        ]);
    }
}
