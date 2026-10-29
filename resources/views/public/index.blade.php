@extends('layouts.app')

@php
    // Judul SEO: hasil pencarian punya judul sendiri; nomor halaman ditambahkan agar tidak kembar.
    $seoTitle = $search !== '' ? 'Hasil pencarian "'.$search.'"' : 'Artikel Kesehatan Jantung';
    if ($articles->currentPage() > 1) {
        $seoTitle .= ' – Halaman '.$articles->currentPage();
    }
@endphp

@section('title', $seoTitle)
@section('description', config('seo.description'))

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
            <h1 class="mx-auto mt-3 max-w-2xl text-3xl font-extrabold tracking-tight text-white sm:text-5xl">Artikel Kesehatan Jantung</h1>
            <p class="mx-auto mt-4 max-w-xl text-base text-brand-200">Kenali gejala, faktor risiko, pencegahan, dan perawatan penyakit jantung bersama {{ config('app.name') }}.</p>

            <form action="{{ route('home') }}" method="GET" class="mx-auto mt-8 flex max-w-xl gap-2 rounded-xl bg-white p-1.5 text-left shadow-xl">
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
                            <span class="badge bg-amber-400 text-amber-950">Terbaru</span>
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
                @if ($sideArticles->isEmpty())
                    <p class="text-sm text-gray-500">Artikel lain akan tampil di sini.</p>
                @else
                    <ul class="divide-y divide-gray-200">
                        @foreach ($sideArticles as $article)
                            <li>
                                <a href="{{ route('public.articles.show', $article) }}" class="group flex gap-4 py-3.5 first:pt-0">
                                    <div class="min-w-0 flex-1">
                                        <h3 class="line-clamp-3 font-serif text-[0.95rem] leading-snug font-semibold text-gray-900 group-hover:text-brand-700">{{ $article->title }}</h3>
                                        <p class="mt-1.5 text-xs text-gray-500"><x-date :value="$article->created_at" /></p>
                                    </div>
                                    <x-article-thumb :article="$article" class="size-20" />
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </aside>
        </div>
    @endif

    {{-- Baris 2 (2 kolom): daftar artikel | Terpopuler + ajakan konsultasi. --}}
    <div class="grid gap-8 lg:grid-cols-3">
        <section id="daftar-artikel" class="min-w-0 scroll-mt-24 lg:col-span-2">
            <x-section-title :title="$listTitle" />
            <p class="-mt-2 mb-6 text-sm text-gray-600">
                @if ($search !== '')
                    {{ $articles->total() }} artikel untuk "<span class="font-semibold text-gray-900">{{ $search }}</span>"
                    · <a href="{{ route('home') }}#daftar-artikel" class="link">Hapus pencarian</a>
                @else
                    {{ $articles->total() }} artikel kesehatan jantung dari {{ config('app.name') }}
                @endif
            </p>

            @if ($articles->isEmpty())
                <div class="card px-6 py-16 text-center">
                    <x-icon name="newspaper" size="size-8" class="text-gray-300" />
                    <p class="mt-3 text-base font-semibold text-gray-900">Belum ada artikel</p>
                    <p class="mt-1 text-sm text-gray-500">
                        {{ $search !== '' ? 'Coba kata kunci lain.' : 'Artikel yang dipublikasikan akan tampil di sini.' }}
                    </p>
                    @if ($search !== '')
                        <a href="{{ route('home') }}" class="btn btn-secondary mt-5">Lihat semua artikel</a>
                    @endif
                </div>
            @else
                <div class="grid gap-6 sm:grid-cols-2">
                    @foreach ($articles as $article)
                        <x-article-card :article="$article" />
                    @endforeach
                </div>
            @endif

            <div class="mt-10">{{ $articles->links() }}</div>
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
@endsection
