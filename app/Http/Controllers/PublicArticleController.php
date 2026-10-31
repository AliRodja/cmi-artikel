<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Support\ArticleViewCounter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class PublicArticleController extends Controller
{
    /** Kolom yang cukup untuk daftar Artikel Terpopuler (tanpa isi artikel yang panjang). */
    private const POPULAR_COLUMNS = ['id', 'title', 'title_en', 'content_en', 'slug', 'image', 'view_count', 'created_at'];

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));

        // 10 per halaman: pas untuk grid 2 kolom di samping kolom Terpopuler.
        $articles = Article::query()
            ->published()
            ->with('user:id,name,profession')
            ->when($search !== '', fn ($query) => $query->search($search))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        // Halaman awal (tanpa pencarian, halaman 1) diawali Sorotan ala portal berita.
        $isLanding = $search === '' && $articles->onFirstPage();

        // 1 artikel terbaru untuk kotak besar + 5 berikutnya untuk kolom kanan.
        $latestArticles = $isLanding
            ? Article::query()
                ->published()
                ->with(['user:id,name,profession', 'medicalReviewer:id,name,profession'])
                ->latest()
                ->take(6)
                ->get()
            : collect();

        $popularArticles = Article::query()->popular()->take(5)->get(self::POPULAR_COLUMNS);

        return view('public.index', compact('articles', 'search', 'isLanding', 'latestArticles', 'popularArticles'));
    }

    /**
     * Live search di panel cari navbar (dipanggil fetch() dari resources/js/live-search.js).
     * Mengembalikan potongan HTML yang dirender Blade, sehingga semua teks sudah di-escape oleh {{ }}
     * dan JavaScript cukup menempelkannya.
     */
    public function liveSearch(Request $request): View|RedirectResponse
    {
        $term = trim((string) $request->query('q'));

        // Dibuka langsung di browser (bukan lewat fetch) → halaman hasil pencarian lengkap di beranda.
        if (! $request->ajax()) {
            return redirect()->route('home', array_filter(['search' => $term]));
        }

        $results = collect();
        $total = 0;

        if (mb_strlen($term) >= 2) {
            $query = Article::query()->published()->search($term);
            $total = (clone $query)->count();

            $results = $query
                // Judul yang cocok tampil lebih dulu, baru yang cocok di isi saja.
                ->orderByRaw("CASE WHEN title LIKE ? ESCAPE '!' OR title_en LIKE ? ESCAPE '!' THEN 0 ELSE 1 END", [Article::likePattern($term), Article::likePattern($term)])
                ->latest()
                ->take(6)
                ->get(Article::LIST_COLUMNS);
        }

        return view('public._live-search-results', compact('term', 'results', 'total'));
    }

    public function show(Request $request, Article $article): View
    {
        abort_unless($article->isPublished(), 404);

        ArticleViewCounter::record($article, $request);

        $article->load([
            'user:id,name,profession',
            'medicalReviewer:id,name,profession',
            // Komentar terlama di atas (urutan percakapan), sekaligus nama & peran penulisnya.
            'comments' => fn ($query) => $query->with('user:id,name,role')->oldest(),
        ]);

        $isBookmarked = (bool) $request->user()?->hasBookmarked($article);

        // Sidebar kanan: daftar artikel terbaru lainnya.
        $latestArticles = Article::query()
            ->published()
            ->whereKeyNot($article->getKey())
            ->latest()
            ->take(5)
            ->get(Article::LIST_COLUMNS);

        // Di bawah artikel: artikel terkait...
        $relatedArticles = $this->relatedArticles($article, $latestArticles->modelKeys());

        // ...dan artikel yang paling sering dilihat, setelah melewati batas minimal kunjungan.
        $popularArticles = Article::query()
            ->popular()
            ->whereKeyNot($article->getKey())
            ->take(6)
            ->get(self::POPULAR_COLUMNS);

        return view('public.show', compact(
            'article', 'latestArticles', 'relatedArticles', 'popularArticles', 'isBookmarked',
        ));
    }

    /**
     * Maksimal 3 artikel terkait: yang membahas kata kunci utama (SEO) artikel ini lebih dulu, sisanya diisi
     * artikel lain secara acak. Artikel yang sudah tampil di sidebar ($exclude) tidak diulang.
     */
    private function relatedArticles(Article $article, array $exclude): Collection
    {
        $others = fn () => Article::query()->published()->whereKeyNot($article->getKey())->with('user:id,name,profession');

        $related = filled($article->focus_keyword)
            ? $others()->search($article->focus_keyword)->latest()->take(3)->get()
            : collect();

        if ($related->count() < 3) {
            $related = $related->concat($others()
                ->whereKeyNot([...$exclude, ...$related->pluck('id')])
                ->inRandomOrder()
                ->take(3 - $related->count())
                ->get());
        }

        return $related;
    }
}
