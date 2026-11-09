@extends('layouts.app')

@section('title', $article->meta_title)
@section('description', $article->meta_description)
@section('og_type', 'article')
@if ($article->image_url)
    @section('og_image', $article->image_url)
@endif

@push('seo-meta')
    <meta property="article:published_time" content="{{ $article->created_at->toAtomString() }}">
    <meta property="article:modified_time" content="{{ $article->updated_at->toAtomString() }}">
    <meta property="article:section" content="Kesehatan Jantung">
@endpush

@push('structured-data')
    <script type="application/ld+json">@json(\App\Support\Seo::article($article), \App\Support\Seo::JSON_FLAGS)</script>
@endpush

@section('hero')
    {{-- Bar progres membaca (CSS scroll-driven animation, lihat .reading-progress di app.css). --}}
    <div class="reading-progress" aria-hidden="true"></div>

    {{-- Header artikel bergaya Health Library (Cleveland Clinic): cerah, bersih, berfokus pada keterbacaan & kredibilitas medis. --}}
    <section class="relative overflow-hidden border-b border-gray-200 bg-gradient-to-b from-brand-50/80 via-brand-50/25 to-white">
        <div class="container-page relative py-10 sm:py-12">
            <nav aria-label="{{ __('Breadcrumb') }}" class="flex flex-wrap items-center gap-2 text-xs font-medium text-gray-600">
                {{-- min-h-11 + -my-3: area sentuh 44px tanpa menambah tinggi baris breadcrumb. --}}
                <a href="{{ route('home') }}" class="-my-3 inline-flex min-h-11 items-center transition hover:text-brand-700 hover:underline">{{ __('Beranda') }}</a>
                <x-icon name="chevron-right" size="size-3" class="text-gray-400" />
                <a href="{{ route('home') }}#daftar-artikel" class="-my-3 inline-flex min-h-11 items-center transition hover:text-brand-700 hover:underline">{{ __('Artikel Kesehatan Jantung') }}</a>
            </nav>

            <div class="mt-4 flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-1.5 rounded-full border border-brand-200 bg-white px-3.5 py-1 text-xs font-semibold text-brand-700 shadow-2xs">
                    <x-icon name="stethoscope" size="size-3.5" class="text-brand-600" />
                    {{ __('Kesehatan Jantung') }}
                </span>

                @if ($article->medicalReviewer)
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-3.5 py-1 text-xs font-semibold text-emerald-800">
                        <x-icon name="shield" size="size-3.5" class="text-emerald-600" />
                        {{ __('Ditinjau oleh :name', ['name' => $article->medicalReviewer->name]) }}
                    </span>
                @endif
            </div>

            <div class="mt-4 max-w-4xl">
                {{-- display_*: versi Inggris bila pengunjung memilih English dan penulis sudah menerjemahkan (Article::showsEnglish),
                     selain itu Indonesia. Atribut lang mengikuti versi yang tampil (lafal pembaca layar). --}}
                <h1 lang="{{ $article->display_locale }}" class="font-article text-3xl leading-[1.2] font-extrabold tracking-tight text-gray-900 sm:text-4xl lg:text-[2.65rem]">
                    {{ $article->display_title }}
                </h1>

                <div class="mt-6 flex flex-wrap items-center gap-x-6 gap-y-3 border-t border-gray-200/80 pt-5 text-sm text-gray-600">
                    <div class="flex items-center gap-3">
                        <x-avatar :name="$article->user?->name" size="md" />
                        <div class="leading-tight">
                            <p class="font-bold text-gray-900">{{ $article->user?->name ?? __('Anonim') }}</p>
                            <p class="mt-0.5 text-xs text-gray-600">{{ $article->user?->profession ?? __('Penulis').' · '.config('app.name') }}</p>
                        </div>
                    </div>

                    <span class="hidden h-7 w-px bg-gray-200 sm:block" aria-hidden="true"></span>

                    <div class="flex flex-wrap items-center gap-x-5 gap-y-2 text-xs font-medium text-gray-600 sm:text-sm">
                        <span class="inline-flex items-center gap-1.5">
                            <x-icon name="calendar" size="size-4" class="text-brand-600" />
                            {{ $article->created_at->translatedFormat('d F Y') }}
                        </span>
                        <span class="inline-flex items-center gap-1.5">
                            <x-icon name="eye" size="size-4" class="text-brand-600" />
                            {{ trans_choice(':count kali dilihat', $article->view_count, ['count' => Number::format($article->view_count)]) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('content')
    {{-- Tiga kolom di layar lebar: aksi (simpan & bagikan) | isi artikel | sidebar. Di ponsel tersusun ke bawah. --}}
    <div class="grid gap-8 lg:grid-cols-[4.75rem_minmax(0,1fr)_20rem] xl:gap-10">
        {{-- Kolom 1: rel aksi. Di layar lebar tersusun ke bawah dan ikut menempel saat scroll. --}}
        <aside aria-label="{{ __('Simpan dan bagikan artikel') }}" class="lg:sticky lg:top-24 lg:self-start">
            <div class="flex flex-wrap items-center gap-2 rounded-2xl border border-gray-200/80 bg-white px-3 py-2.5 shadow-xs lg:flex-col lg:flex-nowrap lg:px-2 lg:py-3.5">
                @include('public._bookmark-button', ['class' => 'share-btn'])
                <span class="mx-1 h-6 w-px bg-gray-200 lg:mx-0 lg:my-0.5 lg:h-px lg:w-8" aria-hidden="true"></span>
                <span class="text-xs font-bold text-gray-600">{{ __('Bagikan') }}</span>
                <x-share-buttons :article="$article" class="lg:flex-col lg:gap-2.5" />
            </div>
        </aside>

        {{-- Kolom 2: isi artikel, penulis & komentar. --}}
        <div class="min-w-0 space-y-8">
            <article class="card overflow-hidden border-t-4 border-t-brand-600">
                @if ($article->image_url)
                    <div class="border-b border-gray-100 bg-brand-50/40">
                        <img src="{{ $article->image_url }}" alt="{{ $article->display_title }}" fetchpriority="high" class="aspect-[16/9] w-full object-cover">
                    </div>
                @endif

                <div class="px-6 py-8 sm:px-10 sm:py-10 lg:px-12">
                    @if (app()->getLocale() === 'en' && ! $article->hasEnglish())
                        {{-- Pengunjung memilih English, tetapi penulis belum menerjemahkan artikel ini. --}}
                        <p class="mb-6 flex items-start gap-2.5 rounded-xl border border-brand-200 bg-brand-50/80 px-4 py-3 text-sm text-brand-900">
                            <x-icon name="globe" size="size-4" class="mt-0.5 shrink-0 text-brand-600" />
                            {{ __('Artikel ini belum tersedia dalam Bahasa Inggris, jadi ditampilkan dalam Bahasa Indonesia.') }}
                        </p>
                    @endif
                    {{-- display_content_html sudah disanitasi (App\Support\ArticleContent), aman untuk {!! !!}. --}}
                    <div lang="{{ $article->display_locale }}" class="article-content article-lead">{!! $article->display_content_html !!}</div>
                </div>

                {{-- Tinjauan medis & sumber rujukan: penanda kredibilitas informasi kesehatan. --}}
                @if ($article->medicalReviewer || $article->source_list)
                    <div class="space-y-6 border-t border-gray-100 bg-gray-50/40 px-6 py-7 sm:px-10 lg:px-12">
                        @include('public._credibility')
                    </div>
                @endif

                {{-- Akhir artikel: ajakan menyimpan & membagikan. --}}
                <footer class="border-t border-gray-200/80 bg-brand-50/45 px-6 py-6 sm:px-10 lg:px-12">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <p class="text-sm font-bold text-gray-900">{{ __('Artikel ini bermanfaat? Bagikan ke keluarga & teman.') }}</p>
                            <p class="mt-0.5 text-xs text-gray-600">{{ __('Bantu orang terdekat mengenali dan menjaga kesehatan jantung lebih dini.') }}</p>
                        </div>
                        <x-share-buttons :article="$article" labels class="flex-wrap" />
                    </div>
                </footer>
            </article>

            @if ($article->user)
                <section class="card flex flex-wrap items-center justify-between gap-4 border-l-4 border-l-brand-600 p-5 sm:p-6">
                    <div class="flex items-center gap-4">
                        <x-avatar :name="$article->user->name" size="md" />
                        <div class="min-w-0">
                            <p class="text-xs font-semibold tracking-wider text-brand-700 uppercase">{{ __('Ditulis oleh') }}</p>
                            <p class="mt-0.5 text-base font-bold text-gray-900">{{ $article->user->name }}</p>
                            <p class="text-sm text-gray-600">{{ $article->user->profession ?? __('Penulis di :site', ['site' => config('app.name')]) }}</p>
                        </div>
                    </div>
                    @include('public._bookmark-button', ['class' => 'btn btn-secondary min-h-11', 'withLabel' => true])
                </section>
            @endif

            {{-- Komentar (Livewire: kirim & hapus komentar tanpa muat ulang halaman) --}}
            <livewire:article-comments :article="$article" />
        </div>

        {{-- Kolom 3: artikel lainnya, ajakan konsultasi (menempel saat scroll). --}}
        <aside class="min-w-0 space-y-6">
            @if ($latestArticles->isNotEmpty())
                <section>
                    <x-section-title :title="__('Artikel Lainnya')" :href="route('home').'#daftar-artikel'" />
                    <div class="card border-t-4 border-t-brand-600 p-5">
                        <ul class="divide-y divide-gray-100">
                            @foreach ($latestArticles as $related)
                                <li>
                                    <a href="{{ route('public.articles.show', $related) }}" class="group flex gap-3.5 py-3.5 first:pt-0 last:pb-0">
                                        <x-article-thumb :article="$related" class="size-16 shrink-0" />
                                        <div class="min-w-0 flex-1">
                                            <p class="line-clamp-2 font-article text-sm leading-snug font-semibold text-gray-900 transition group-hover:text-brand-700">{{ $related->display_title }}</p>
                                            <p class="mt-1.5 text-xs text-gray-500">{{ $related->created_at->translatedFormat('d M Y') }}</p>
                                        </div>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </section>
            @endif

            <x-consultation-card class="lg:sticky lg:top-24" />
        </aside>
    </div>

    {{-- Artikel terkait: membahas kata kunci utama yang sama, dilengkapi artikel lain (PublicArticleController::relatedArticles). --}}
    @if ($relatedArticles->isNotEmpty())
        <section class="mt-16">
            <x-section-title :title="__('Artikel Terkait')" :href="route('home').'#daftar-artikel'" :link-label="__('Semua artikel')" />
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($relatedArticles as $related)
                    <x-article-card :article="$related" />
                @endforeach
            </div>
        </section>
    @endif

    {{-- Artikel terpopuler: urut jumlah pembaca (articles.view_count), minimal config('clinic.popular_min_views'). --}}
    <section class="mt-16">
        <div class="mb-5 flex flex-wrap items-center justify-between gap-2 border-b border-gray-200 pb-3">
            <h2 class="border-l-4 border-brand-600 pl-3 text-lg leading-tight font-bold text-gray-900">
                {{ __('Artikel Terpopuler') }}
            </h2>
            <p class="text-xs font-medium text-gray-500">{{ __('Berdasarkan jumlah pembaca') }}</p>
        </div>

        @if ($popularArticles->isEmpty())
            <div class="card px-6 py-10 text-center">
                <x-icon name="chart-line" size="size-7" class="text-gray-300" />
                <p class="mt-2 text-sm text-gray-500">
                    {{ __('Belum ada artikel populer.') }}
                    {{ __('Artikel akan muncul di sini setelah dibaca minimal :count kali.', ['count' => config('clinic.popular_min_views')]) }}
                </p>
            </div>
        @else
            <ol class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($popularArticles as $popular)
                    <li><x-popular-article :article="$popular" :rank="$loop->iteration" /></li>
                @endforeach
            </ol>
        @endif
    </section>
@endsection

@push('scripts')
    <script>
        // Tombol salin tautan (components/share-buttons, bisa lebih dari satu di halaman):
        // ikon tautan berganti centang selama 2 detik; label ikut berubah (teksnya dari data-text-*, ikut bahasa).
        document.querySelectorAll('[data-copy-link]').forEach((button) => {
            const icon = button.querySelector('[data-copy-icon]');
            const done = button.querySelector('[data-copy-done]');
            const label = button.querySelector('[data-copy-label]');

            button.addEventListener('click', async () => {
                try {
                    await navigator.clipboard.writeText(button.dataset.copyLink);
                    icon.hidden = true;
                    done.hidden = false;
                    label.textContent = button.dataset.textCopied;
                } catch {
                    label.textContent = button.dataset.textFailed;
                }
                setTimeout(() => {
                    icon.hidden = false;
                    done.hidden = true;
                    label.textContent = button.dataset.textCopy;
                }, 2000);
            });
        });
    </script>
@endpush
