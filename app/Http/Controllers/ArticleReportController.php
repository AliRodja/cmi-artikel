<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ArticleReportController extends Controller
{
    public function __invoke(Request $request): View
    {
        $search = trim((string) $request->query('search'));

        $articles = DB::table('articles')
            ->join('users', 'articles.user_id', '=', 'users.id')
            ->select([
                'articles.id',
                'articles.title',
                'articles.slug',
                'articles.status',
                'articles.created_at',
                'users.name as author_name',
                'users.email as author_email',
            ])
            ->where('articles.status', Article::STATUS_PUBLISHED)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('articles.title', 'like', "%{$search}%")
                        ->orWhere('users.name', 'like', "%{$search}%")
                        ->orWhere('users.email', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('articles.created_at')
            ->paginate(10)
            ->withQueryString();

        return view('articles.report', compact('articles', 'search'));
    }
}
