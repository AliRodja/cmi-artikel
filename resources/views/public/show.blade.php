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

    {{-- Hero biru cerah khas CMI, sama dengan beranda (public/index) & footer. --}}
    <section class="relative overflow-hidden bg-brand-600">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_85%_20%,var(--color-brand-400),transparent_45%)] opacity-45"></div>

        <div class="container-page relative py-12 sm:py-16">
            <nav aria-label="{{ __('Breadcrumb') }}" class="flex flex-wrap items-center gap-2 text-xs font-medium text-white/90">
                <a href="{{ route('home') }}" class="hover:text-white hover:underline">{{ __('Beranda') }}</a>
                <x-icon name="chevron-right" size="size-3" class="text-white/70" />
                <a href="{{ route('home') }}#daftar-artikel" class="hover:text-white hover:underline">{{ __('Artikel Kesehatan Jantung') }}</a>
            </nav>

            <div class="mt-6 max-w-4xl">
                {{-- display_*: versi Inggris bila pengunjung memilih English dan penulis sudah menerjemahkan (Article::showsEnglish),
                     selain itu Indonesia. Atribut lang mengikuti versi yang tampil (lafal pembaca layar). --}}
                <h1 lang="{{ $article->display_locale }}" class="text-3xl leading-[1.15] font-extrabold tracking-tight text-white sm:text-4xl lg:text-[2.75rem]">
                    {{ $article->display_title }}
                </h1>

                <div class="mt-8 flex flex-wrap items-center gap-x-5 gap-y-3 text-sm text-white/90">
                    <div class="flex items-center gap-3">
                        <x-avatar :name="$article->user?->name" size="md" tone="dark" />
                        <div class="leading-tight">
                            <p class="font-semibold text-white">{{ $article->user?->name ?? __('Anonim') }}</p>
                            <p class="text-xs text-white/90">{{ $article->user?->profession ?? __('Penulis').' · '.config('app.name') }}</p>
                        </div>
                    </div>
                    <span class="hidden h-8 w-px bg-white/25 sm:block"></span>
                    <span class="inline-flex items-center gap-1.5">
                        <x-icon name="calendar" size="size-4" class="text-white/90" />
                        {{ $article->created_at->translatedFormat('d F Y') }}
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <x-icon name="eye" size="size-4" class="text-white/90" />
                        {{ trans_choice(':count kali dilihat', $article->view_count, ['count' => Number::format($article->view_count)]) }}
                    </span>
                    @if ($article->medicalReviewer)
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-white/15 px-3 py-1 text-white ring-1 ring-white/30">
                            <x-icon name="shield" size="size-4" class="text-amber-300" />
                            {{ __('Ditinjau oleh :name', ['name' => $article->medicalReviewer->name]) }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection

@section('content')
    {{-- Tiga kolom di layar lebar: aksi (simpan & bagikan) | isi artikel | sidebar. Di ponsel tersusun ke bawah. --}}
    <div class="grid gap-8 lg:grid-cols-[3rem_minmax(0,1fr)_18rem] xl:gap-12">
        {{-- Kolom 1: rel aksi. Di layar lebar tersusun ke bawah dan ikut menempel saat scroll. --}}
        <aside aria-label="{{ __('Simpan dan bagikan artikel') }}" class="lg:sticky lg:top-24 lg:self-start">
            <div class="flex flex-wrap items-center gap-2 lg:flex-col lg:gap-3">
                @include('public._bookmark-button', ['class' => 'share-btn'])
                <span class="mx-1 h-6 w-px bg-gray-200 lg:mx-0 lg:h-px lg:w-6"></span>
                <span class="text-[0.75rem] font-semibold tracking-wide text-gray-500 uppercase">{{ __('Bagikan') }}</span>
                <x-share-buttons :article="$article" class="lg:flex-col lg:gap-3" />
            </div>
        </aside>

        {{-- Kolom 2: isi artikel, penulis & komentar. --}}
        <div class="min-w-0 space-y-8">
            <article class="card overflow-hidden">
                @if ($article->image_url)
                    <img src="{{ $article->image_url }}" alt="{{ $article->display_title }}" class="aspect-[16/9] w-full object-cover">
                @endif

                <div class="px-6 py-8 sm:px-12 sm:py-10">
                    @if (app()->getLocale() === 'en' && ! $article->hasEnglish())
                        {{-- Pengunjung memilih English, tetapi penulis belum menerjemahkan artikel ini. --}}
                        <p class="mb-6 flex items-start gap-2 rounded-lg bg-brand-50 px-4 py-3 text-sm text-brand-900">
                            <x-icon name="globe" size="size-4" class="mt-0.5 shrink-0 text-brand-600" />
                            {{ __('Artikel ini belum tersedia dalam Bahasa Inggris, jadi ditampilkan dalam Bahasa Indonesia.') }}
                        </p>
                    @endif
                    {{-- display_content_html sudah disanitasi (App\Support\ArticleContent), aman untuk {!! !!}. --}}
                    <div lang="{{ $article->display_locale }}" class="article-content article-lead">{!! $article->display_content_html !!}</div>
                </div>

                {{-- Tinjauan medis & sumber rujukan: penanda kredibilitas informasi kesehatan. --}}
                @if ($article->medicalReviewer || $article->source_list)
                    <div class="space-y-6 border-t border-gray-100 px-6 py-6 sm:px-12">
                        @include('public._credibility')
                    </div>
                @endif

                {{-- Akhir artikel: ajakan menyimpan & membagikan. --}}
                <footer class="border-t border-gray-100 bg-gray-50/70 px-6 py-5 sm:px-12">
                    <p class="text-sm font-semibold text-gray-900">{{ __('Artikel ini bermanfaat? Bagikan ke keluarga & teman.') }}</p>
                    <x-share-buttons :article="$article" labels class="mt-3 flex-wrap" />
                </footer>
            </article>

            @if ($article->user)
                <section class="card flex items-center gap-4 p-5 sm:p-6">
                    <x-avatar :name="$article->user->name" size="md" />
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-semibold tracking-wider text-gray-500 uppercase">{{ __('Ditulis oleh') }}</p>
                        <p class="font-bold text-gray-900">{{ $article->user->name }}</p>
                        <p class="text-sm text-gray-600">{{ $article->user->profession ?? __('Penulis di :site', ['site' => config('app.name')]) }}</p>
                    </div>
                    @include('public._bookmark-button', ['class' => 'btn btn-secondary btn-sm', 'withLabel' => true])
                </section>
            @endif

            {{-- Komentar --}}
            <section id="komentar" class="card scroll-mt-24 p-5 sm:p-6">
                <h2 class="flex items-center gap-2 text-lg font-bold text-gray-900">
                    <x-icon name="comments" class="text-brand-600" /> {{ __('Komentar') }}
                    <span class="rounded-full bg-gray-100 px-2 text-sm font-semibold text-gray-600">{{ $article->comments->count() }}</span>
                </h2>

                @auth
                    <form action="{{ route('comments.store', $article) }}" method="POST" class="mt-5 flex gap-3">
                        @csrf
                        <x-avatar :name="auth()->user()->name" />
                        <div class="min-w-0 flex-1">
                            <label for="body" class="sr-only">{{ __('Tulis komentar') }}</label>
                            <textarea id="body" name="body" rows="3" maxlength="2000" required
                                      placeholder="{{ __('Tulis komentar atau pertanyaan Anda...') }}"
                                      @class(['form-input', 'form-input-error' => $errors->has('body')])>{{ old('body') }}</textarea>
                            @error('body')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                            <div class="mt-3 flex justify-end">
                                <button type="submit" class="btn btn-primary btn-sm">{{ __('Kirim Komentar') }}</button>
                            </div>
                        </div>
                    </form>
                @else
                    <div class="mt-5 rounded-lg bg-brand-50 px-4 py-3 text-sm text-brand-900">
                        <a href="{{ route('login') }}" class="link">{{ __('Masuk') }}</a> {{ __('atau') }}
                        <a href="{{ route('register') }}" class="link">{{ __('daftar') }}</a> {{ __('untuk ikut berkomentar.') }}
                    </div>
                @endauth

                @if ($article->comments->isEmpty())
                    <p class="mt-6 text-sm text-gray-500">{{ __('Belum ada komentar. Jadilah yang pertama berkomentar.') }}</p>
                @else
                    <ul class="mt-6 space-y-4">
                        @foreach ($article->comments as $comment)
                            <li class="flex gap-3">
                                <x-avatar :name="$comment->user->name" />
                                <div class="min-w-0 flex-1 rounded-xl border border-gray-200 bg-white px-4 py-3">
                                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                        <p class="text-sm font-semibold text-gray-900">{{ $comment->user->name }}</p>
                                        @if ($comment->user_id === $article->user_id)
                                            <span class="badge bg-brand-50 text-brand-700">{{ __('Penulis artikel') }}</span>
                                        @elseif ($comment->user->isAdmin())
                                            <span class="badge bg-gray-100 text-gray-600">{{ __('Admin') }}</span>
                                        @endif
                                        <span class="text-xs text-gray-500">· <x-date :value="$comment->created_at" format="d M Y, H:i" /></span>

                                        @can('delete', $comment)
                                            <form action="{{ route('comments.destroy', $comment) }}" method="POST" class="ml-auto"
                                                  data-confirm="{{ __('Hapus komentar ini?') }}" onsubmit="return confirm(this.dataset.confirm)">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="{{ __('Hapus komentar') }}" class="cursor-pointer rounded-md p-2 text-gray-500 hover:bg-red-50 hover:text-red-600">
                                                    <x-icon name="trash" size="size-3" />
                                                    <span class="sr-only">{{ __('Hapus komentar') }}</span>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                    {{-- Teks biasa ({{ }} = di-escape), baris baru dipertahankan dengan whitespace-pre-line. --}}
                                    <p class="mt-1 text-sm whitespace-pre-line text-gray-700">{{ $comment->body }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>

        {{-- Kolom 3: artikel lainnya, ajakan konsultasi (menempel saat scroll). --}}
        <aside class="min-w-0 space-y-6">
            @if ($latestArticles->isNotEmpty())
                <section class="card p-5">
                    <h2 class="font-bold text-gray-900">{{ __('Artikel Lainnya') }}</h2>
                    <ul class="mt-2 divide-y divide-gray-100">
                        @foreach ($latestArticles as $related)
                            <li>
                                <a href="{{ route('public.articles.show', $related) }}" class="group flex gap-3 py-3">
                                    <x-article-thumb :article="$related" class="size-16" />
                                    <div class="min-w-0">
                                        <p class="line-clamp-2 text-sm leading-snug font-semibold text-gray-900 group-hover:text-brand-700">{{ $related->display_title }}</p>
                                        <p class="mt-1 text-xs text-gray-500">{{ $related->created_at->translatedFormat('d M Y') }}</p>
                                    </div>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            <x-consultation-card class="lg:sticky lg:top-24" />
        </aside>
    </div>

    {{-- Artikel terkait: membahas kata kunci utama yang sama, dilengkapi artikel lain (PublicArticleController::relatedArticles). --}}
    @if ($relatedArticles->isNotEmpty())
        <section class="mt-16">
            <div class="mb-6 flex flex-wrap items-end justify-between gap-2 border-b border-gray-200 pb-3">
                <h2 class="text-xl font-bold text-gray-900">{{ __('Artikel Terkait') }}</h2>
                <a href="{{ route('home') }}#daftar-artikel" class="link inline-flex items-center gap-1.5 text-sm">
                    {{ __('Semua artikel') }} <x-icon name="chevron-right" size="size-3" />
                </a>
            </div>
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($relatedArticles as $related)
                    <x-article-card :article="$related" />
                @endforeach
            </div>
        </section>
    @endif

    {{-- Artikel terpopuler: urut jumlah pembaca (articles.view_count), minimal config('clinic.popular_min_views'). --}}
    <section class="mt-16">
        <div class="mb-6 flex flex-wrap items-end justify-between gap-2 border-b border-gray-200 pb-3">
            <h2 class="flex items-center gap-2 text-xl font-bold text-gray-900">
                <x-icon name="fire" class="text-orange-500" /> {{ __('Artikel Terpopuler') }}
            </h2>
            <p class="text-xs text-gray-500">{{ __('Berdasarkan jumlah pembaca') }}</p>
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
