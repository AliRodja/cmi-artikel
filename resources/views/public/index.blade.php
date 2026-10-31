@extends('layouts.app')

@php
    // Judul SEO: hasil pencarian punya judul sendiri; nomor halaman ditambahkan agar tidak kembar.
    $seoTitle = $search !== '' ? __('Hasil pencarian ":term"', ['term' => $search]) : __('Artikel Kesehatan Jantung');
    if ($articles->currentPage() > 1) {
        $seoTitle .= ' – '.__('Halaman :page', ['page' => $articles->currentPage()]);
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
    @php
        $quickTopics = [
            __('Hipertensi'),
            __('Kolesterol'),
            __('Nyeri Dada'),
            __('Aritmia'),
            __('Gaya Hidup'),
        ];
    @endphp

    <section class="relative overflow-hidden border-b border-brand-900 bg-brand-950">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_50%_0%,var(--color-brand-800),transparent_65%)] opacity-80"></div>

        {{-- Konten utama hero: identitas editorial, judul, deskripsi, pencarian, dan pintasan topik kronis. --}}
        <div class="container-page relative pt-14 pb-12 text-center sm:pt-20 sm:pb-16">
            <div class="inline-flex items-center gap-2 rounded-full border border-amber-300/30 bg-white/5 px-4 py-1.5">
                <x-icon name="shield" size="size-3" class="text-amber-300" />
                <p class="eyebrow text-amber-300">{{ __('Pusat Informasi') }} · {{ config('app.name') }}</p>
            </div>

            <h1 class="mx-auto mt-5 max-w-3xl font-article text-3xl leading-tight font-bold tracking-tight text-white sm:text-5xl">{{ __('Artikel Kesehatan Jantung') }}</h1>
            <p class="mx-auto mt-4 max-w-2xl text-base leading-relaxed text-brand-100">{{ __('Kenali gejala, faktor risiko, pencegahan, dan perawatan penyakit jantung bersama :site.', ['site' => config('app.name')]) }}</p>

            <form action="{{ route('home') }}" method="GET" class="mx-auto mt-8 flex max-w-2xl items-center gap-2 rounded-xl bg-white p-2 text-left shadow-2xl ring-1 ring-white/20">
                <x-icon name="magnifying-glass" size="size-4" class="ml-2.5 text-gray-500" />
                <label for="search" class="sr-only">{{ __('Cari artikel') }}</label>
                <input type="search" id="search" name="search" value="{{ $search }}" placeholder="{{ __('Cari judul atau isi artikel...') }}"
                       class="w-full rounded-lg border-0 bg-transparent px-2 text-sm text-gray-900 placeholder:text-gray-500 focus:ring-0 focus:outline-none">
                <button type="submit" class="btn btn-primary shrink-0 px-5 py-2.5">{{ __('Cari') }}</button>
            </form>

            {{-- Pintasan topik untuk pembaca dengan riwayat kondisi kronis / pencegahan jangka panjang. --}}
            <div class="mx-auto mt-5 flex max-w-2xl flex-wrap items-center justify-center gap-2">
                <span class="mr-1 text-xs font-medium text-brand-200">{{ __('Topik utama:') }}</span>
                @foreach ($quickTopics as $topic)
                    <a href="{{ route('home', ['search' => $topic]) }}#daftar-artikel"
                       @class([
                           'rounded-full border px-3.5 py-1.5 text-xs font-medium transition',
                           'border-amber-300 bg-amber-400/20 text-white' => strcasecmp($search, $topic) === 0,
                           'border-white/20 bg-white/5 text-brand-100 hover:border-amber-300/60 hover:bg-white/10 hover:text-white' => strcasecmp($search, $topic) !== 0,
                       ])>
                        {{ $topic }}
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Pilar kredibilitas medis: memberi kepastian kualitas informasi bagi pembaca. --}}
        <div class="relative border-t border-white/10 bg-brand-900/40">
            <div class="container-page grid gap-6 py-6 sm:grid-cols-3 sm:gap-8">
                <div class="flex items-start gap-3.5">
                    <span class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-white/10 ring-1 ring-amber-300/30">
                        <x-icon name="shield" size="size-4" class="text-amber-300" />
                    </span>
                    <div>
                        <p class="text-sm font-semibold text-white">{{ __('Ditinjau Tim Medis') }}</p>
                        <p class="mt-0.5 text-xs leading-relaxed text-brand-200">{{ __('Setiap pembahasan mengutamakan akurasi klinis dan keselamatan pasien.') }}</p>
                    </div>
                </div>

                <div class="flex items-start gap-3.5">
                    <span class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-white/10 ring-1 ring-amber-300/30">
                        <x-icon name="document-text" size="size-4" class="text-amber-300" />
                    </span>
                    <div>
                        <p class="text-sm font-semibold text-white">{{ __('Berbasis Rujukan Ilmiah') }}</p>
                        <p class="mt-0.5 text-xs leading-relaxed text-brand-200">{{ __('Disusun dari literatur kardiologi dan pedoman kesehatan tepercaya.') }}</p>
                    </div>
                </div>

                <div class="flex items-start gap-3.5">
                    <span class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-white/10 ring-1 ring-amber-300/30">
                        <x-icon name="stethoscope" size="size-4" class="text-amber-300" />
                    </span>
                    <div>
                        <p class="text-sm font-semibold text-white">{{ __('Pendampingan Jangka Panjang') }}</p>
                        <p class="mt-0.5 text-xs leading-relaxed text-brand-200">{{ __('Fokus pada pengendalian faktor risiko kronis dan kualitas hidup.') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('content')
    @php
        $listTitle = match (true) {
            $search !== '' => __('Hasil Pencarian'),
            $isLanding => __('Semua Artikel'),
            default => __('Artikel Terbaru'),
        };
        $featured = $latestArticles->first();
        $sideArticles = $latestArticles->slice(1);
    @endphp

    @if ($isLanding && $featured)
        {{-- Baris 1 (2 kolom): Editorial Split Card artikel terbaru | Artikel Lainnya. --}}
        <div class="mb-14 grid gap-8 lg:grid-cols-3">
            <section class="lg:col-span-2">
                <x-section-title :title="__('Sorotan')" />
                <article class="group card overflow-hidden border-gray-200/90 transition duration-300 hover:border-brand-300 hover:shadow-lg">
                    <div class="h-1 bg-gradient-to-r from-brand-900 via-brand-600 to-amber-400"></div>

                    <a href="{{ route('public.articles.show', $featured) }}" class="block aspect-[16/9] w-full overflow-hidden bg-brand-950">
                        @if ($featured->image_url)
                            <img src="{{ $featured->image_url }}" alt="{{ $featured->display_title }}"
                                 class="size-full object-cover transition duration-500 group-hover:scale-105">
                        @else
                            <div class="flex size-full items-center justify-center bg-gradient-to-br from-brand-800 to-brand-950">
                                <span class="text-6xl font-extrabold text-white/15">{{ Str::upper(Str::substr($featured->display_title, 0, 1)) }}</span>
                            </div>
                        @endif
                    </a>

                    <div class="p-6 sm:p-8">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="badge bg-amber-100 text-amber-900 ring-1 ring-amber-300/70">{{ __('Terbaru') }}</span>
                            @if ($featured->medicalReviewer)
                                <span class="badge bg-brand-50 text-brand-800 ring-1 ring-brand-200/80">
                                    <x-icon name="shield" size="size-3" class="mr-1.5 text-brand-700" />
                                    {{ __('Ditinjau oleh :name', ['name' => $featured->medicalReviewer->name]) }}
                                </span>
                            @endif
                            <span class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-500 sm:ml-auto">
                                <x-icon name="calendar" size="size-3" class="text-brand-600" />
                                <x-date :value="$featured->created_at" format="d F Y" />
                            </span>
                        </div>

                        <h3 class="mt-4 font-article text-2xl leading-snug font-bold text-gray-900 transition group-hover:text-brand-700 sm:text-3xl">
                            <a href="{{ route('public.articles.show', $featured) }}">{{ $featured->display_title }}</a>
                        </h3>

                        <p class="mt-3 line-clamp-3 text-base leading-relaxed text-gray-600">{{ $featured->display_excerpt }}</p>

                        <div class="mt-6 flex flex-wrap items-center justify-between gap-4 border-t border-gray-100 pt-5">
                            <div class="flex items-center gap-3">
                                <x-avatar :name="$featured->user?->name" size="md" />
                                <div class="leading-tight">
                                    <p class="text-sm font-bold text-gray-900">{{ $featured->user?->name ?? __('Anonim') }}</p>
                                    <p class="mt-0.5 text-xs text-gray-500">{{ $featured->user?->profession ?? __('Penulis di :site', ['site' => config('app.name')]) }}</p>
                                </div>
                            </div>

                            <a href="{{ route('public.articles.show', $featured) }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-brand-700 transition group-hover:text-brand-600">
                                {{ __('Baca Selengkapnya') }} <x-icon name="chevron-right" size="size-3" />
                            </a>
                        </div>
                    </div>
                </article>
            </section>

            <aside>
                <x-section-title :title="__('Artikel Lainnya')" :href="route('home').'#daftar-artikel'" />
                <div class="card p-5 sm:p-6">
                    @if ($sideArticles->isEmpty())
                        <p class="text-sm text-gray-500">{{ __('Artikel lain akan tampil di sini.') }}</p>
                    @else
                        <ul class="divide-y divide-gray-100">
                            @foreach ($sideArticles as $article)
                                <li>
                                    <a href="{{ route('public.articles.show', $article) }}" class="group flex gap-4 py-3.5 first:pt-0 last:pb-0">
                                        <div class="min-w-0 flex-1">
                                            <h3 class="line-clamp-3 font-article text-[0.95rem] leading-snug font-semibold text-gray-900 transition group-hover:text-brand-700">{{ $article->display_title }}</h3>
                                            <p class="mt-1.5 flex flex-wrap items-center gap-x-2 gap-y-0.5 text-xs text-gray-500">
                                                <span><x-date :value="$article->created_at" /></span>
                                                @if ($article->medical_reviewer_id)
                                                    <span class="inline-flex items-center gap-1 font-medium text-brand-700">
                                                        · <x-icon name="shield" size="size-3" /> {{ __('Ditinjau Medis') }}
                                                    </span>
                                                @endif
                                            </p>
                                        </div>
                                        <x-article-thumb :article="$article" class="size-20" />
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </aside>
        </div>
    @endif

    {{-- Baris 2 (2 kolom): daftar artikel | Terpopuler + ajakan konsultasi. --}}
    <div class="grid gap-8 lg:grid-cols-3">
        <section id="daftar-artikel" class="min-w-0 scroll-mt-24 lg:col-span-2">
            <x-section-title :title="$listTitle" />
            <p class="-mt-2 mb-6 text-sm text-gray-600">
                @if ($search !== '')
                    {{ trans_choice(':count artikel untuk', $articles->total()) }} "<span class="font-semibold text-gray-900">{{ $search }}</span>"
                    · <a href="{{ route('home') }}#daftar-artikel" class="link">{{ __('Hapus pencarian') }}</a>
                @else
                    {{ trans_choice(':count artikel kesehatan jantung dari :site', $articles->total(), ['site' => config('app.name')]) }}
                @endif
            </p>

            @if ($articles->isEmpty())
                <div class="card px-6 py-16 text-center">
                    <x-icon name="newspaper" size="size-8" class="text-gray-300" />
                    <p class="mt-3 text-base font-semibold text-gray-900">{{ __('Belum ada artikel') }}</p>
                    <p class="mt-1 text-sm text-gray-500">
                        {{ $search !== '' ? __('Coba kata kunci lain.') : __('Artikel yang dipublikasikan akan tampil di sini.') }}
                    </p>
                    @if ($search !== '')
                        <a href="{{ route('home') }}" class="btn btn-secondary mt-5">{{ __('Lihat semua artikel') }}</a>
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
                <x-section-title :title="__('Terpopuler')" />
                <div class="card p-5 sm:p-6">
                    @if ($popularArticles->isEmpty())
                        <p class="text-sm text-gray-500">{{ __('Artikel akan muncul di sini setelah dibaca minimal :count kali.', ['count' => config('clinic.popular_min_views')]) }}</p>
                    @else
                        <ol class="divide-y divide-gray-100">
                            @foreach ($popularArticles as $popular)
                                <li>
                                    <a href="{{ route('public.articles.show', $popular) }}" class="group flex gap-4 py-3.5 first:pt-0 last:pb-0">
                                        <span class="w-6 shrink-0 font-article text-2xl leading-none font-bold text-brand-700/75 tabular-nums transition group-hover:text-brand-700">{{ $loop->iteration }}</span>
                                        <div class="min-w-0">
                                            <h3 class="line-clamp-3 font-article text-[0.95rem] leading-snug font-semibold text-gray-900 transition group-hover:text-brand-700">{{ $popular->display_title }}</h3>
                                            <p class="mt-1.5 inline-flex items-center gap-1 text-xs text-gray-500">
                                                <x-icon name="eye" size="size-3" /> {{ trans_choice(':count kali dilihat', $popular->view_count, ['count' => Number::format($popular->view_count)]) }}
                                            </p>
                                        </div>
                                    </a>
                                </li>
                            @endforeach
                        </ol>
                    @endif
                </div>
            </section>

            <x-consultation-card class="lg:sticky lg:top-24" />
        </aside>
    </div>
@endsection
