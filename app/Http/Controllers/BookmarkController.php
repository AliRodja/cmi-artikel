<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** "Baca Nanti": artikel yang disimpan user (tabel pivot bookmarks). */
class BookmarkController extends Controller
{
    public function index(Request $request): View
    {
        $articles = $request->user()->bookmarks()
            ->published() // artikel yang kemudian ditarik admin tidak ikut tampil
            ->with('user:id,name')
            ->orderByPivot('created_at', 'desc')
            ->paginate(9);

        return view('bookmarks.index', compact('articles'));
    }

    public function toggle(Request $request, Article $article): RedirectResponse
    {
        abort_unless($article->isPublished(), 404);

        // toggle(): simpan bila belum ada, hapus bila sudah ada — satu tombol untuk dua aksi.
        $result = $request->user()->bookmarks()->toggle($article->getKey());

        return back()->with('success', $result['attached']
            ? 'Artikel disimpan ke Baca Nanti.'
            : 'Artikel dihapus dari Baca Nanti.');
    }
}
