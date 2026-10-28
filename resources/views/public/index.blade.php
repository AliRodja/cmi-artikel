@extends('layouts.app')

@section('title', 'Berita & Artikel')

@section('hero')
    <section class="relative overflow-hidden bg-brand-950">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_85%_20%,var(--color-brand-700),transparent_45%)] opacity-70"></div>
        <div class="pointer-events-none absolute inset-0 bg-[linear-gradient(to_right,rgb(255_255_255/0.04)_1px,transparent_1px),linear-gradient(to_bottom,rgb(255_255_255/0.04)_1px,transparent_1px)] bg-[size:48px_48px]"></div>

        <div class="container-page relative py-16 sm:py-20">
            <p class="eyebrow text-brand-300">Pusat Informasi</p>
            <h1 class="mt-3 max-w-2xl text-3xl font-extrabold tracking-tight text-white sm:text-5xl">Berita &amp; Artikel Kesehatan</h1>
            <p class="mt-4 max-w-xl text-base text-brand-200">Informasi kesehatan, kabar terbaru, dan pengumuman resmi dari {{ config('app.name') }}.</p>

            <form action="{{ route('home') }}" method="GET" class="mt-8 flex max-w-xl gap-2 rounded-xl bg-white p-1.5 shadow-xl">
                @if ($category)
                    <input type="hidden" name="jenis" value="{{ $category }}">
                @endif
                <label for="search" class="sr-only">Cari artikel</label>
                <input type="search" id="search" name="search" value="{{ $search }}" placeholder="Cari judul atau isi artikel..."
                       class="w-full rounded-lg border-0 bg-transparent px-3 text-sm text-gray-900 placeholder:text-gray-400 focus:ring-0 focus:outline-none">
                <button type="submit" class="btn btn-primary shrink-0">Cari</button>
            </form>
        </div>
    </section>
@endsection

@section('content')
    @php
        // Tampilan "featured" hanya di halaman pertama tanpa pencarian & tanpa filter jenis.
        $showFeatured = $search === '' && ! $category && $articles->onFirstPage() && $articles->count() >= 3;
        $items = collect($articles->items());
        $featured = $showFeatured ? $items->first() : null;
        $highlights = $showFeatured ? $items->slice(1, 2) : collect();
        $gridArticles = $showFeatured ? $items->slice(3) : $items;
    @endphp

    {{-- Filter jenis artikel (?jenis=...). Kata kunci pencarian ikut terbawa. --}}
    <nav class="mb-8 flex gap-2 overflow-x-auto pb-1" aria-label="Jenis artikel">
        @foreach (['' => 'Semua'] + \App\Models\Article::CATEGORIES as $value => $label)
            <a href="{{ route('home', array_filter(['jenis' => $value, 'search' => $search], 'filled')) }}"
               @class([
                   'shrink-0 rounded-full border px-4 py-1.5 text-sm font-medium transition',
                   'border-brand-600 bg-brand-600 text-white' => $category === ($value ?: null),
                   'border-gray-200 bg-white text-gray-700 hover:border-brand-300 hover:text-brand-700' => $category !== ($value ?: null),
               ])>{{ $label }}</a>
        @endforeach
    </nav>

    @if ($search !== '')
        <div class="mb-8 flex flex-wrap items-center justify-between gap-3">
            <p class="text-sm text-gray-600">
                Ditemukan <span class="font-semibold text-gray-900">{{ $articles->total() }}</span> artikel untuk
                "<span class="font-semibold text-gray-900">{{ $search }}</span>"
            </p>
            <a href="{{ route('home') }}" class="link text-sm">Hapus pencarian</a>
        </div>
    @endif

    @if ($articles->isEmpty())
        <div class="card px-6 py-16 text-center">
            <p class="text-base font-semibold text-gray-900">Belum ada artikel</p>
            <p class="mt-1 text-sm text-gray-500">
                {{ $search !== '' ? 'Coba kata kunci lain.' : 'Artikel yang dipublikasikan akan tampil di sini.' }}
            </p>
        </div>
    @else
        @if ($featured)
            <section class="mb-14 grid gap-6 lg:grid-cols-3">
                <article class="group card relative overflow-hidden lg:col-span-2">
                    <a href="{{ route('public.articles.show', $featured) }}" class="block h-full">
                        <div class="aspect-[16/9] h-full min-h-72 w-full overflow-hidden bg-brand-900 lg:aspect-auto">
                            @if ($featured->image_url)
                                <img src="{{ $featured->image_url }}" alt="{{ $featured->title }}"
                                     class="size-full object-cover transition duration-500 group-hover:scale-105">
                            @else
                                <div class="size-full bg-gradient-to-br from-brand-700 to-brand-950"></div>
                            @endif
                        </div>
                        <div class="absolute inset-0 bg-gradient-to-t from-brand-950/95 via-brand-950/50 to-transparent"></div>
                        <div class="absolute inset-x-0 bottom-0 p-6 sm:p-8">
                            <span class="badge bg-amber-400 text-amber-950">Terbaru</span>
                            <h2 class="mt-3 text-2xl leading-tight font-extrabold text-white sm:text-3xl">{{ $featured->title }}</h2>
                            <p class="mt-3 line-clamp-2 max-w-2xl text-sm text-brand-100">{{ $featured->excerpt }}</p>
                            <p class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs font-medium text-brand-200">
                                <span class="inline-flex items-center gap-1.5"><x-icon name="user" size="size-3" /> {{ $featured->user?->name ?? 'Anonim' }}</span>
                                <span class="inline-flex items-center gap-1.5"><x-icon name="calendar" size="size-3" /> {{ $featured->created_at->translatedFormat('d F Y') }}</span>
                                <span class="inline-flex items-center gap-1.5"><x-icon name="clock" size="size-3" /> {{ $featured->reading_time }} menit baca</span>
                            </p>
                        </div>
                    </a>
                </article>

                <div class="grid gap-6">
                    @foreach ($highlights as $article)
                        <article class="group card flex flex-col justify-between p-6 transition hover:shadow-lg">
                            <div>
                                <p class="inline-flex items-center gap-1 text-xs font-medium text-gray-500">
                                    <x-icon name="calendar" size="size-3" class="text-gray-400" /> {{ $article->created_at->translatedFormat('d F Y') }}
                                </p>
                                <h3 class="mt-2 line-clamp-3 text-lg leading-snug font-bold text-gray-900 group-hover:text-brand-700">
                                    <a href="{{ route('public.articles.show', $article) }}">{{ $article->title }}</a>
                                </h3>
                                <p class="mt-2 line-clamp-2 text-sm text-gray-600">{{ $article->excerpt }}</p>
                            </div>
                            <a href="{{ route('public.articles.show', $article) }}" class="link mt-4 text-sm">Baca selengkapnya →</a>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($gridArticles->isNotEmpty())
            @if ($featured)
                <div class="mb-6 flex items-end justify-between border-b border-gray-200 pb-3">
                    <h2 class="text-xl font-bold text-gray-900">Artikel Lainnya</h2>
                </div>
            @endif

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($gridArticles as $article)
                    <x-article-card :article="$article" />
                @endforeach
            </div>
        @endif

        <div class="mt-10">{{ $articles->links() }}</div>
    @endif
@endsection
