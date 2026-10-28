<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Support\ArticleViewCounter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicArticleController extends Controller
{
    /** Kolom yang cukup untuk daftar Artikel Terpopuler (tanpa isi artikel yang panjang). */
    private const POPULAR_COLUMNS = ['id', 'title', 'slug', 'category', 'image', 'view_count', 'created_at'];

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        // ?jenis=jantung → hanya artikel jenis itu; nilai yang tidak dikenal diabaikan.
        $category = array_key_exists((string) $request->query('jenis'), Article::CATEGORIES)
            ? (string) $request->query('jenis')
            : null;

        $articles = Article::query()
            ->published()
            ->with('user:id,name')
            ->when($category, fn ($query) => $query->ofCategory($category))
            ->when($search !== '', fn ($query) => $query->search($search))
            ->latest()
            ->paginate(9)
            ->withQueryString();

        // Halaman awal (tanpa cari/filter, halaman 1) bertata letak ala portal berita: Sorotan, Berita per Jenis,
        // Terpopuler. Saat mencari/memfilter, yang tampil hanya daftar hasil.
        $isLanding = $search === '' && $category === null && $articles->onFirstPage();

        $latestArticles = collect();
        $categorySections = collect();
        $popularArticles = collect();

        if ($isLanding) {
            // 1 artikel terbaru untuk kotak besar + 5 berikutnya untuk kolom kanan.
            $latestArticles = Article::query()->published()->with('user:id,name')->latest()->take(6)->get();

            // Berita per jenis: 4 terbaru tiap jenis (1 judul utama + 3 judul terkait). Jenis tanpa artikel dilewati.
            $categorySections = collect(Article::CATEGORIES)
                ->map(fn (string $label, string $key) => Article::query()
                    ->published()
                    ->ofCategory($key)
                    ->with('user:id,name')
                    ->latest()
                    ->take(4)
                    ->get())
                ->filter(fn ($items) => $items->isNotEmpty());

            $popularArticles = Article::query()->popular()->take(5)->get(self::POPULAR_COLUMNS);
        }

        $categoryCounts = Article::publishedCountsByCategory();

        return view('public.index', compact(
            'articles', 'search', 'category', 'isLanding', 'latestArticles', 'categorySections', 'popularArticles', 'categoryCounts',
        ));
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
                ->orderByRaw('CASE WHEN title LIKE ? THEN 0 ELSE 1 END', ["%{$term}%"])
                ->latest()
                ->take(6)
                ->get(['id', 'title', 'slug', 'category', 'image', 'created_at']);
        }

        return view('public._live-search-results', compact('term', 'results', 'total'));
    }

    public function show(Request $request, Article $article): View
    {
        abort_unless($article->isPublished(), 404);

        ArticleViewCounter::record($article, $request);

        $article->load([
            'user:id,name',
            // Komentar terlama di atas (urutan percakapan), sekaligus nama & peran penulisnya.
            'comments' => fn ($query) => $query->with('user:id,name,role')->oldest(),
        ]);

        $isBookmarked = (bool) $request->user()?->hasBookmarked($article);

        // Sidebar kanan: daftar artikel terbaru lainnya + jumlah artikel per jenis.
        $latestArticles = Article::query()
            ->published()
            ->whereKeyNot($article->getKey())
            ->latest()
            ->take(5)
            ->get(['id', 'title', 'slug', 'category', 'image', 'created_at']);

        // Di bawah artikel: artikel lain dengan jenis yang sama...
        $relatedArticles = $article->category
            ? Article::query()
                ->published()
                ->ofCategory($article->category)
                ->whereKeyNot($article->getKey())
                ->with('user:id,name')
                ->latest()
                ->take(3)
                ->get()
            : collect();

        // ...dan artikel yang paling sering dilihat, setelah melewati batas minimal kunjungan.
        $popularArticles = Article::query()
            ->popular()
            ->whereKeyNot($article->getKey())
            ->take(6)
            ->get(self::POPULAR_COLUMNS);

        $categoryCounts = Article::publishedCountsByCategory();

        return view('public.show', compact(
            'article', 'latestArticles', 'relatedArticles', 'popularArticles', 'categoryCounts', 'isBookmarked',
        ));
    }
}
