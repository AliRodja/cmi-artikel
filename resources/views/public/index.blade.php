@extends('layouts.app')

@php
    // Judul & deskripsi SEO: halaman jenis punya judul sendiri; nomor halaman ditambahkan agar tidak kembar.
    $categoryLabel = $category ? \App\Models\Article::CATEGORIES[$category] : null;
    $seoTitle = match (true) {
        $search !== '' => 'Hasil pencarian "'.$search.'"',
        $categoryLabel !== null => 'Artikel Kesehatan '.$categoryLabel,
        default => 'Berita & Artikel Kesehatan',
    };
    if ($articles->currentPage() > 1) {
        $seoTitle .= ' – Halaman '.$articles->currentPage();
    }
    $seoDescription = $categoryLabel
        ? 'Kumpulan artikel kesehatan seputar '.Str::lower($categoryLabel).' dari '.config('app.name').': gejala, pencegahan, dan perawatan.'
        : config('seo.description');
@endphp

@section('title', $seoTitle)
@section('description', $seoDescription)

@if ($isLanding)
    @push('structured-data')
        <script type="application/ld+json">@json(\App\Support\Seo::organization(), \App\Support\Seo::JSON_FLAGS)</script>
    @endpush
@endif

@section('hero')
    <section class="relative overflow-hidden bg-brand-950">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_50%_0%,var(--color-brand-700),transparent_60%)] opacity-70"></div>

        {{-- Konten hero di tengah: label, judul, deskripsi, kolom pencarian. --}}
        <div class="container-page relative py-16 text-center sm:py-20">
            <p class="eyebrow text-brand-300">Pusat Informasi</p>
            <h1 class="mx-auto mt-3 max-w-2xl text-3xl font-extrabold tracking-tight text-white sm:text-5xl">Berita &amp; Artikel Kesehatan</h1>
            <p class="mx-auto mt-4 max-w-xl text-base text-brand-200">Informasi kesehatan, kabar terbaru, dan pengumuman resmi dari {{ config('app.name') }}.</p>

            <form action="{{ route('home') }}" method="GET" class="mx-auto mt-8 flex max-w-xl gap-2 rounded-xl bg-white p-1.5 text-left shadow-xl">
                @if ($category)
                    <input type="hidden" name="jenis" value="{{ $category }}">
                @endif
                <label for="search" class="sr-only">Cari artikel</label>
                <input type="search" id="search" name="search" value="{{ $search }}" placeholder="Cari judul atau isi artikel..."
                       class="w-full rounded-lg border-0 bg-transparent px-3 text-sm text-gray-900 placeholder:text-gray-500 focus:ring-0 focus:outline-none">
                <button type="submit" class="btn btn-primary shrink-0">Cari</button>
            </form>
        </div>
    </section>
@endsection

@section('content')
    @php
        $listTitle = match (true) {
            $search !== '' => 'Hasil Pencarian',
            $category !== null => 'Artikel '.\App\Models\Article::CATEGORIES[$category],
            $isLanding => 'Semua Artikel',
            default => 'Artikel Terbaru',
        };
        $featured = $latestArticles->first();
        $sideArticles = $latestArticles->slice(1);
    @endphp

    @if ($isLanding && $featured)
        {{-- Baris 1 (2 kolom): kotak besar artikel terbaru | Artikel Lainnya. --}}
        <div class="mb-14 grid gap-8 lg:grid-cols-3">
            <section class="lg:col-span-2">
                <x-section-title title="Sorotan" />
                <article class="group card relative overflow-hidden">
                    <a href="{{ route('public.articles.show', $featured) }}" class="block">
                        <div class="aspect-[16/10] w-full overflow-hidden bg-brand-900 sm:aspect-[16/9]">
                            @if ($featured->image_url)
                                <img src="{{ $featured->image_url }}" alt="{{ $featured->title }}"
                                     class="size-full object-cover transition duration-500 group-hover:scale-105">
                            @else
                                <div class="size-full bg-gradient-to-br from-brand-700 to-brand-950"></div>
                            @endif
                        </div>
                        <div class="absolute inset-0 bg-gradient-to-t from-brand-950/95 via-brand-950/40 to-transparent"></div>
                        <div class="absolute inset-x-0 bottom-0 p-6 sm:p-8">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="badge bg-amber-400 text-amber-950">Terbaru</span>
                                <x-category-badge :article="$featured" />
                            </div>
                            <h3 class="mt-3 font-serif text-2xl leading-tight font-semibold text-white sm:text-[2rem]">{{ $featured->title }}</h3>
                            <p class="mt-3 line-clamp-2 hidden max-w-2xl text-sm text-brand-100 sm:block">{{ $featured->excerpt }}</p>
                            <p class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs font-medium text-brand-200">
                                <span class="inline-flex items-center gap-1.5"><x-icon name="user" size="size-3" /> {{ $featured->user?->name ?? 'Anonim' }}</span>
                                <span class="inline-flex items-center gap-1.5"><x-icon name="calendar" size="size-3" /> <x-date :value="$featured->created_at" format="d F Y" /></span>
                            </p>
                        </div>
                    </a>
                </article>
            </section>

            <aside>
                <x-section-title title="Artikel Lainnya" :href="route('home').'#daftar-artikel'" />
                <ul class="divide-y divide-gray-200">
                    @foreach ($sideArticles as $article)
                        <li>
                            <a href="{{ route('public.articles.show', $article) }}" class="group flex gap-4 py-3.5 first:pt-0">
                                <div class="min-w-0 flex-1">
                                    @if ($article->category_label)
                                        <p class="text-xs font-semibold text-brand-700">{{ $article->category_label }}</p>
                                    @endif
                                    <h3 class="mt-0.5 line-clamp-3 font-serif text-[0.95rem] leading-snug font-semibold text-gray-900 group-hover:text-brand-700">{{ $article->title }}</h3>
                                    <p class="mt-1.5 text-xs text-gray-500"><x-date :value="$article->created_at" /></p>
                                </div>
                                <x-article-thumb :article="$article" class="size-20" />
                            </a>
                        </li>
                    @endforeach
                </ul>
            </aside>
        </div>

        {{-- Baris 2 (2 kolom): Berita per Jenis | Terpopuler + konsultasi. --}}
        <div class="mb-14 grid gap-8 lg:grid-cols-3">
            <section class="min-w-0 lg:col-span-2">
                <x-section-title title="Berita Utama" />
                <div class="divide-y divide-gray-200">
                    @foreach ($categorySections as $key => $items)
                        @php($lead = $items->first())
                        <article class="grid gap-6 py-6 first:pt-0 md:grid-cols-[minmax(0,1fr)_15rem]">
                            <div class="min-w-0">
                                <a href="{{ route('home', ['jenis' => $key]) }}#daftar-artikel"
                                   class="inline-flex items-center gap-1 text-sm font-semibold text-brand-700 hover:text-brand-600">
                                    {{ \App\Models\Article::CATEGORIES[$key] }} <x-icon name="chevron-right" size="size-3" />
                                </a>
                                <h3 class="mt-2 font-serif text-2xl leading-snug font-semibold text-gray-900">
                                    <a href="{{ route('public.articles.show', $lead) }}" class="hover:text-brand-700">{{ $lead->title }}</a>
                                </h3>
                                <p class="mt-2 line-clamp-2 text-sm text-gray-600">{{ $lead->excerpt }}</p>
                                <p class="mt-2 text-xs text-gray-500">{{ $lead->user?->name ?? 'Anonim' }} · <x-date :value="$lead->created_at" format="d F Y" /></p>

                                @if ($items->count() > 1)
                                    <ul class="mt-4 divide-y divide-gray-100 border-t border-gray-100">
                                        @foreach ($items->slice(1) as $article)
                                            <li>
                                                <a href="{{ route('public.articles.show', $article) }}"
                                                   class="block py-2.5 font-serif text-[0.95rem] leading-snug font-semibold text-gray-800 hover:text-brand-700">{{ $article->title }}</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                            <a href="{{ route('public.articles.show', $lead) }}" class="order-first block md:order-none" tabindex="-1" aria-hidden="true">
                                <x-article-thumb :article="$lead" class="aspect-[4/3] w-full rounded-xl" />
                            </a>
                        </article>
                    @endforeach
                </div>
            </section>

            <aside class="space-y-8">
                <section>
                    <x-section-title title="Terpopuler" />
                    @if ($popularArticles->isEmpty())
                        <p class="text-sm text-gray-500">Artikel akan muncul di sini setelah dibaca minimal {{ config('clinic.popular_min_views') }} kali.</p>
                    @else
                        <ol class="divide-y divide-gray-200">
                            @foreach ($popularArticles as $popular)
                                <li>
                                    <a href="{{ route('public.articles.show', $popular) }}" class="group flex gap-4 py-3.5 first:pt-0">
                                        <span class="w-6 shrink-0 font-serif text-3xl leading-none font-semibold text-brand-400 tabular-nums group-hover:text-brand-600">{{ $loop->iteration }}</span>
                                        <div class="min-w-0">
                                            <h3 class="line-clamp-3 font-serif text-[0.95rem] leading-snug font-semibold text-gray-900 group-hover:text-brand-700">{{ $popular->title }}</h3>
                                            <p class="mt-1.5 inline-flex items-center gap-1 text-xs text-gray-500">
                                                <x-icon name="eye" size="size-3" /> {{ number_format($popular->view_count, 0, ',', '.') }} kali dilihat
                                            </p>
                                        </div>
                                    </a>
                                </li>
                            @endforeach
                        </ol>
                    @endif
                </section>

                <x-consultation-card class="lg:sticky lg:top-24" />
            </aside>
        </div>
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
                           'flex shrink-0 items-center gap-2 rounded-full border px-4 py-2.5 text-sm font-medium transition',
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
        @else
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($articles as $article)
                    <x-article-card :article="$article" />
                @endforeach
            </div>
        @endif

        <div class="mt-10">{{ $articles->links() }}</div>
    </section>

    {{-- Saat mencari / memfilter, ajakan konsultasi tetap ada di bawah hasil. --}}
    @unless ($isLanding)
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
               class="btn shrink-0 bg-[#0E7A3F] px-6 py-3 text-white shadow-sm hover:bg-[#0B6534] focus-visible:ring-white">
                <x-icon name="whatsapp" size="size-5" /> Konsultasi via WhatsApp
            </a>
        </section>
    @endunless
@endsection
