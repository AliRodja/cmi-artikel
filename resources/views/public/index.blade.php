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

        // Poster jumbotron di public/images/ yang namanya diawali "hero" (mis. Hero-1.webp, Hero-2.webp, dst.).
        $heroImages = is_dir(public_path('images'))
            ? collect(\Illuminate\Support\Facades\File::files(public_path('images')))
                ->filter(fn ($file) => preg_match('/^hero.*\.(webp|png|jpe?g|avif)$/i', $file->getFilename()))
                ->sort(fn ($a, $b) => strnatcasecmp($a->getFilename(), $b->getFilename()))
                ->map(fn ($file) => asset('images/'.$file->getFilename()))
                ->values()
            : collect();
    @endphp

    <section aria-label="{{ __('Pusat Informasi') }}" class="bg-white">
        {{-- 1. Paling atas: Jumbotron poster yang bisa digeser ke kiri & kanan (tanpa tindihan warna biru). --}}
        @if ($heroImages->isNotEmpty())
            <div data-hero-slider aria-label="{{ __('Banner informasi :site', ['site' => config('app.name')]) }}"
                 class="relative mx-auto max-w-[1920px] overflow-hidden border-b border-gray-200 bg-white">
                <div data-hero-track class="flex snap-x snap-mandatory overflow-x-auto scroll-smooth [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                    @foreach ($heroImages as $imageUrl)
                        <div data-hero-slide class="w-full shrink-0 snap-center">
                            <img src="{{ $imageUrl }}"
                                 alt="{{ __('Banner informasi :site :number', ['site' => config('app.name'), 'number' => $loop->iteration]) }}"
                                 @if (! $loop->first) loading="lazy" @endif
                                 class="block h-auto w-full object-contain select-none">
                        </div>
                    @endforeach
                </div>

                @if ($heroImages->count() > 1)
                    <button type="button" data-hero-prev
                            title="{{ __('Slide sebelumnya') }}"
                            class="absolute top-1/2 left-2 z-10 flex size-9 -translate-y-1/2 cursor-pointer items-center justify-center rounded-full bg-gray-900/65 text-white shadow-md backdrop-blur-xs transition hover:bg-brand-600 focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:outline-none sm:left-4 sm:size-11">
                        <x-icon name="chevron-left" size="size-4" />
                        <span class="sr-only">{{ __('Slide sebelumnya') }}</span>
                    </button>

                    <button type="button" data-hero-next
                            title="{{ __('Slide berikutnya') }}"
                            class="absolute top-1/2 right-2 z-10 flex size-9 -translate-y-1/2 cursor-pointer items-center justify-center rounded-full bg-gray-900/65 text-white shadow-md backdrop-blur-xs transition hover:bg-brand-600 focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:outline-none sm:right-4 sm:size-11">
                        <x-icon name="chevron-right" size="size-4" />
                        <span class="sr-only">{{ __('Slide berikutnya') }}</span>
                    </button>

                    <div class="pointer-events-none absolute inset-x-0 bottom-2 z-10 flex justify-center sm:bottom-3">
                        <div class="pointer-events-auto inline-flex items-center gap-1.5 rounded-full bg-gray-900/55 px-2.5 py-1 backdrop-blur-xs">
                            @foreach ($heroImages as $imageUrl)
                                <button type="button" data-hero-dot="{{ $loop->index }}"
                                        aria-label="{{ __('Slide :number', ['number' => $loop->iteration]) }}"
                                        aria-current="{{ $loop->first ? 'true' : 'false' }}"
                                        class="h-2 cursor-pointer rounded-full transition-all duration-300 aria-[current=true]:w-6 aria-[current=true]:bg-amber-300 aria-[current=false]:w-2 aria-[current=false]:bg-white/75 hover:aria-[current=false]:bg-white"></button>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @endif

        {{-- 2. Di bawah gambar poster: blok pencarian & kartu akses cepat bergaya portal rumah sakit (Cleveland Clinic). --}}
        <div class="border-b border-gray-200 bg-gradient-to-b from-brand-50/75 via-brand-50/30 to-white">
            <div class="container-page py-10 sm:py-12">
                <div class="mx-auto max-w-3xl text-center">
                    

                    <h1 class="mt-4 font-article text-3xl leading-tight font-bold tracking-tight text-gray-900 sm:text-4xl">{{ __('Artikel Kesehatan Jantung') }}</h1>
                    <p class="mx-auto mt-3 max-w-2xl text-base leading-relaxed text-gray-600">{{ __('Kenali gejala, faktor risiko, pencegahan, dan perawatan penyakit jantung bersama :site.', ['site' => config('app.name')]) }}</p>

                    <form action="{{ route('home') }}" method="GET" class="mx-auto mt-7 flex max-w-2xl items-center gap-2 rounded-full border-2 border-brand-200 bg-white p-1.5 pl-4 text-left shadow-md transition focus-within:border-brand-600 focus-within:ring-4 focus-within:ring-brand-500/15">
                        <x-icon name="magnifying-glass" size="size-4" class="shrink-0 text-brand-600" />
                        <label for="search" class="sr-only">{{ __('Cari artikel') }}</label>
                        <input type="search" id="search" name="search" value="{{ $search }}" placeholder="{{ __('Cari judul atau isi artikel...') }}"
                               class="w-full rounded-full border-0 bg-transparent px-2 text-sm text-gray-900 placeholder:text-gray-500 focus:ring-0 focus:outline-none">
                        <button type="submit" class="btn btn-primary shrink-0 rounded-full px-6 py-2.5">{{ __('Cari') }}</button>
                    </form>

                    {{-- Pintasan topik untuk pembaca dengan riwayat kondisi kronis / pencegahan jangka panjang. --}}
                    <div class="mx-auto mt-5 flex max-w-2xl flex-wrap items-center justify-center gap-2">
                        <span class="mr-1 text-xs font-semibold text-gray-600">{{ __('Topik utama:') }}</span>
                        @foreach ($quickTopics as $topic)
                            <a href="{{ route('home', ['search' => $topic]) }}#daftar-artikel"
                               @class([
                                   'rounded-full border px-3.5 py-1.5 text-xs font-semibold transition',
                                   'border-brand-600 bg-brand-600 text-white shadow-xs' => strcasecmp($search, $topic) === 0,
                                   'border-brand-200 bg-white text-gray-700 hover:border-brand-600 hover:bg-brand-600 hover:text-white' => strcasecmp($search, $topic) !== 0,
                               ])>
                                {{ $topic }}
                            </a>
                        @endforeach
                    </div>
                </div>

                {{-- 3 kartu akses cepat bergaya Cleveland Clinic (We're here when you need us). --}}
                <div class="mt-9 grid gap-4 sm:grid-cols-3 sm:gap-6">
                    <a href="{{ config('clinic.whatsapp_consultation_url') }}" target="_blank" rel="noopener noreferrer"
                       class="group card flex flex-col justify-between border-t-4 border-t-[#25D366] p-5 transition duration-200 hover:-translate-y-0.5 hover:shadow-md">
                        <div>
                            <div class="flex items-center justify-between gap-3">
                                <span class="flex size-10 items-center justify-center rounded-lg bg-[#25D366]/12 text-[#1EBE5D]">
                                    <x-icon name="whatsapp" size="size-5" />
                                </span>
                                <x-icon name="chevron-right" size="size-4" class="text-gray-400 transition group-hover:translate-x-0.5 group-hover:text-[#1EBE5D]" />
                            </div>
                            <h2 class="mt-3.5 text-base font-bold text-gray-900 group-hover:text-brand-700">{{ __('Konsultasi Langsung') }}</h2>
                            <p class="mt-1 text-xs leading-relaxed text-gray-600">{{ __('Hubungi tim layanan pasien kami melalui WhatsApp untuk jadwal & keluhan jantung.') }}</p>
                        </div>
                        <span class="mt-4 inline-flex items-center gap-1 text-xs font-semibold text-[#1EBE5D]">
                            {{ __('Hubungi via WhatsApp') }}
                        </span>
                    </a>

                    <a href="#daftar-artikel"
                       class="group card flex flex-col justify-between border-t-4 border-t-brand-600 p-5 transition duration-200 hover:-translate-y-0.5 hover:shadow-md">
                        <div>
                            <div class="flex items-center justify-between gap-3">
                                <span class="flex size-10 items-center justify-center rounded-lg bg-brand-50 text-brand-600">
                                    <x-icon name="stethoscope" size="size-5" />
                                </span>
                                <x-icon name="chevron-right" size="size-4" class="text-gray-400 transition group-hover:translate-x-0.5 group-hover:text-brand-600" />
                            </div>
                            <h2 class="mt-3.5 text-base font-bold text-gray-900 group-hover:text-brand-700">{{ __('Pustaka Kesehatan Jantung') }}</h2>
                            <p class="mt-1 text-xs leading-relaxed text-gray-600">{{ __('Telusuri artikel gejala, faktor risiko, pencegahan, dan perawatan jantung.') }}</p>
                        </div>
                        <span class="mt-4 inline-flex items-center gap-1 text-xs font-semibold text-brand-700">
                            {{ __('Telusuri Artikel') }}
                        </span>
                    </a>

                    <a href="#hubungi-kami"
                       class="group card flex flex-col justify-between border-t-4 border-t-sky-500 p-5 transition duration-200 hover:-translate-y-0.5 hover:shadow-md">
                        <div>
                            <div class="flex items-center justify-between gap-3">
                                <span class="flex size-10 items-center justify-center rounded-lg bg-sky-50 text-sky-600">
                                    <x-icon name="map-pin" size="size-5" />
                                </span>
                                <x-icon name="chevron-right" size="size-4" class="text-gray-400 transition group-hover:translate-x-0.5 group-hover:text-sky-600" />
                            </div>
                            <h2 class="mt-3.5 text-base font-bold text-gray-900 group-hover:text-brand-700">{{ __('Lokasi & Kontak Klinik') }}</h2>
                            <p class="mt-1 text-xs leading-relaxed text-gray-600">{{ __('Lihat alamat lengkap, nomor telepon resmi, dan jam operasional klinik.') }}</p>
                        </div>
                        <span class="mt-4 inline-flex items-center gap-1 text-xs font-semibold text-sky-700">
                            {{ __('Lihat Informasi Kontak') }}
                        </span>
                    </a>
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
                <article class="group card overflow-hidden border-t-4 border-gray-200/90 border-t-brand-600 transition duration-300 hover:border-brand-300 hover:border-t-brand-600 hover:shadow-lg">
                    <a href="{{ route('public.articles.show', $featured) }}" class="block aspect-[16/9] w-full overflow-hidden bg-brand-50">
                        @if ($featured->image_url)
                            <img src="{{ $featured->image_url }}" alt="{{ $featured->display_title }}"
                                 class="size-full object-cover transition duration-500 group-hover:scale-105">
                        @else
                            <div class="flex size-full items-center justify-center bg-gradient-to-br from-brand-500 to-brand-700">
                                <span class="text-6xl font-extrabold text-white/25">{{ Str::upper(Str::substr($featured->display_title, 0, 1)) }}</span>
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
                <div class="card border-t-4 border-t-brand-600 p-5 sm:p-6">
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
                <div class="card border-t-4 border-t-brand-600 p-5 sm:p-6">
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

@push('scripts')
    <script>
        // Jumbotron slider di beranda: mendukung geser sentuh, tombol panah kiri/kanan, titik indikator, & geser otomatis.
        document.querySelectorAll('[data-hero-slider]').forEach((slider) => {
            const track = slider.querySelector('[data-hero-track]');
            const slides = Array.from(slider.querySelectorAll('[data-hero-slide]'));
            const dots = Array.from(slider.querySelectorAll('[data-hero-dot]'));
            const prevBtn = slider.querySelector('[data-hero-prev]');
            const nextBtn = slider.querySelector('[data-hero-next]');

            if (!track || slides.length <= 1) {
                return;
            }

            let activeIndex = 0;

            const setActiveDot = (index) => {
                activeIndex = index;
                dots.forEach((dot, i) => dot.setAttribute('aria-current', String(i === index)));
            };

            const goToSlide = (index) => {
                const targetIndex = (index + slides.length) % slides.length;
                track.scrollTo({ left: targetIndex * track.clientWidth, behavior: 'smooth' });
                setActiveDot(targetIndex);
            };

            prevBtn?.addEventListener('click', () => goToSlide(activeIndex - 1));
            nextBtn?.addEventListener('click', () => goToSlide(activeIndex + 1));
            dots.forEach((dot, i) => dot.addEventListener('click', () => goToSlide(i)));

            // Sinkronkan indikator titik saat pengguna menggeser gambar dengan jari / trackpad.
            track.addEventListener('scroll', () => {
                if (track.clientWidth > 0) {
                    const index = Math.round(track.scrollLeft / track.clientWidth);
                    if (index !== activeIndex && index >= 0 && index < slides.length) {
                        setActiveDot(index);
                    }
                }
            }, { passive: true });

            // Geser otomatis tiap 6 detik (berhenti saat kursor di atas banner atau bila pengguna memilih kurangi gerakan).
            if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                let timer = setInterval(() => goToSlide(activeIndex + 1), 6000);
                const pause = () => clearInterval(timer);
                const resume = () => {
                    clearInterval(timer);
                    timer = setInterval(() => goToSlide(activeIndex + 1), 6000);
                };

                slider.addEventListener('mouseenter', pause);
                slider.addEventListener('mouseleave', resume);
                slider.addEventListener('focusin', pause);
                slider.addEventListener('focusout', resume);
            }
        });
    </script>
@endpush
