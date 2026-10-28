@extends('layouts.app')

@section('title', 'Berita & Artikel')

@section('hero')
    <section class="relative overflow-hidden bg-brand-950">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_85%_20%,var(--color-brand-700),transparent_45%)] opacity-70"></div>

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
        // Sorotan (1 besar + 2 kecil) hanya di halaman awal, diambil dari 3 artikel terbaru halaman ini.
        $items = collect($articles->items());
        $showFeatured = $isLanding && $items->count() >= 3;
        $featured = $showFeatured ? $items->first() : null;
        $highlights = $showFeatured ? $items->slice(1, 2) : collect();
        $gridArticles = $showFeatured ? $items->slice(3) : $items;

        $listTitle = match (true) {
            $search !== '' => 'Hasil Pencarian',
            $category !== null => 'Artikel '.\App\Models\Article::CATEGORIES[$category],
            default => 'Artikel Terbaru',
        };
    @endphp

    {{-- Sorotan --}}
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
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="badge bg-amber-400 text-amber-950">Terbaru</span>
                            <x-category-badge :article="$featured" />
                        </div>
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
                    <article class="group card flex flex-col p-5 transition hover:-translate-y-0.5 hover:shadow-lg">
                        <div class="flex gap-4">
                            <div class="min-w-0 flex-1">
                                <x-category-badge :article="$article" class="mb-2" />
                                <h3 class="line-clamp-3 text-base leading-snug font-bold text-gray-900 group-hover:text-brand-700">
                                    <a href="{{ route('public.articles.show', $article) }}">{{ $article->title }}</a>
                                </h3>
                            </div>
                            <x-article-thumb :article="$article" class="size-20" />
                        </div>
                        <p class="mt-3 line-clamp-2 text-sm text-gray-600">{{ $article->excerpt }}</p>
                        <p class="mt-auto flex items-center justify-between gap-2 pt-4 text-xs text-gray-500">
                            <span class="inline-flex items-center gap-1"><x-icon name="calendar" size="size-3" class="text-gray-400" /> {{ $article->created_at->translatedFormat('d F Y') }}</span>
                            <a href="{{ route('public.articles.show', $article) }}" class="link text-xs">Baca →</a>
                        </p>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Artikel Terpopuler (hanya halaman awal, bila sudah ada yang melewati batas minimal kunjungan). --}}
    @if ($popularArticles->isNotEmpty())
        <section class="mb-14">
            <div class="mb-6 flex flex-wrap items-end justify-between gap-2 border-b border-gray-200 pb-3">
                <h2 class="flex items-center gap-2 text-xl font-bold text-gray-900">
                    <x-icon name="fire" class="text-orange-500" /> Artikel Terpopuler
                </h2>
                <p class="text-xs text-gray-500">Paling banyak dibaca</p>
            </div>
            <ol class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($popularArticles as $popular)
                    {{-- Di ponsel cukup 3 teratas, agar Artikel Terbaru tidak terdorong terlalu jauh. --}}
                    <li @class(['hidden sm:block' => $loop->iteration > 3])>
                        <x-popular-article :article="$popular" :rank="$loop->iteration" />
                    </li>
                @endforeach
            </ol>
        </section>
    @endif

    {{-- Daftar artikel + filter jenis --}}
    <section id="daftar-artikel" class="scroll-mt-24">
        <div class="mb-6 flex flex-col gap-4 border-b border-gray-200 pb-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-900">{{ $listTitle }}</h2>
                <p class="mt-1 text-sm text-gray-500">
                    @if ($search !== '')
                        {{ $articles->total() }} artikel untuk "<span class="font-semibold text-gray-900">{{ $search }}</span>"
                        · <a href="{{ route('home', array_filter(['jenis' => $category], 'filled')) }}" class="link">Hapus pencarian</a>
                    @else
                        {{ $articles->total() }} artikel kesehatan dari {{ config('app.name') }}
                    @endif
                </p>
            </div>

            {{-- Filter jenis (?jenis=...). Kata kunci pencarian ikut terbawa. --}}
            <nav class="-mx-4 flex gap-2 overflow-x-auto px-4 pb-1 sm:mx-0 sm:px-0" aria-label="Jenis artikel">
                @foreach (['' => 'Semua'] + \App\Models\Article::CATEGORIES as $value => $label)
                    @php($isActive = $category === ($value ?: null))
                    <a href="{{ route('home', array_filter(['jenis' => $value, 'search' => $search], 'filled')) }}#daftar-artikel"
                       @if ($isActive) aria-current="page" @endif
                       @class([
                           'flex shrink-0 items-center gap-2 rounded-full border px-4 py-1.5 text-sm font-medium transition',
                           'border-brand-600 bg-brand-600 text-white' => $isActive,
                           'border-gray-200 bg-white text-gray-700 hover:border-brand-300 hover:text-brand-700' => ! $isActive,
                       ])>
                        {{ $label }}
                        <span @class([
                            'rounded-full px-1.5 text-xs',
                            'bg-white/20 text-white' => $isActive,
                            'bg-gray-100 text-gray-500' => ! $isActive,
                        ])>{{ $value === '' ? $categoryCounts->sum() : $categoryCounts->get($value, 0) }}</span>
                    </a>
                @endforeach
            </nav>
        </div>

        @if ($articles->isEmpty())
            <div class="card px-6 py-16 text-center">
                <x-icon name="newspaper" size="size-8" class="text-gray-300" />
                <p class="mt-3 text-base font-semibold text-gray-900">Belum ada artikel</p>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $search !== '' || $category !== null ? 'Coba kata kunci atau jenis lain.' : 'Artikel yang dipublikasikan akan tampil di sini.' }}
                </p>
                @if ($search !== '' || $category !== null)
                    <a href="{{ route('home') }}" class="btn btn-secondary mt-5">Lihat semua artikel</a>
                @endif
            </div>
        @elseif ($gridArticles->isNotEmpty())
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($gridArticles as $article)
                    <x-article-card :article="$article" />
                @endforeach
            </div>
        @endif

        <div class="mt-10">{{ $articles->links() }}</div>
    </section>

    {{-- Ajakan konsultasi: membuka chat WhatsApp customer service (URL di config/clinic.php). --}}
    <section class="mt-16 flex flex-col items-start gap-6 rounded-2xl bg-brand-900 px-6 py-8 text-white sm:px-10 md:flex-row md:items-center md:justify-between">
        <div class="flex items-start gap-4">
            <span class="flex size-12 shrink-0 items-center justify-center rounded-full bg-white/10">
                <x-icon name="stethoscope" size="size-6" />
            </span>
            <div>
                <h2 class="text-xl font-bold">Punya keluhan kesehatan?</h2>
                <p class="mt-1 max-w-xl text-sm text-brand-100">Konsultasikan segera dengan tim {{ config('app.name') }} melalui WhatsApp. Kami siap membantu Anda.</p>
            </div>
        </div>
        <a href="{{ config('clinic.whatsapp_consultation_url') }}" target="_blank" rel="noopener noreferrer"
           class="btn shrink-0 bg-[#25D366] px-6 py-3 text-white shadow-sm hover:bg-[#1DA851] focus-visible:ring-white">
            <x-icon name="whatsapp" size="size-5" /> Konsultasi via WhatsApp
        </a>
    </section>
@endsection
