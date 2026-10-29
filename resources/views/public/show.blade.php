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
    @if ($article->category_label)
        <meta property="article:section" content="{{ $article->category_label }}">
    @endif
@endpush

@push('structured-data')
    <script type="application/ld+json">@json(\App\Support\Seo::article($article), \App\Support\Seo::JSON_FLAGS)</script>
@endpush

@section('hero')
    {{-- Bar progres membaca (CSS scroll-driven animation, lihat .reading-progress di app.css). --}}
    <div class="reading-progress" aria-hidden="true"></div>

    {{-- Hero biru tua, sama dengan beranda (public/index). --}}
    <section class="relative overflow-hidden bg-brand-950">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_85%_20%,var(--color-brand-700),transparent_45%)] opacity-70"></div>

        <div class="container-page relative py-12 sm:py-16">
            <nav aria-label="Breadcrumb" class="flex flex-wrap items-center gap-2 text-xs font-medium text-brand-300">
                <a href="{{ route('home') }}" class="hover:text-white">Beranda</a>
                <x-icon name="chevron-right" size="size-3" class="text-brand-500" />
                <a href="{{ route('home') }}" class="hover:text-white">Berita &amp; Artikel</a>
                @if ($article->category_label)
                    <x-icon name="chevron-right" size="size-3" class="text-brand-500" />
                    <a href="{{ route('home', ['jenis' => $article->category]) }}" class="hover:text-white">{{ $article->category_label }}</a>
                @endif
            </nav>

            <div class="mt-6 max-w-4xl">
                <x-category-badge :article="$article" />
                <h1 class="mt-3 text-3xl leading-[1.15] font-extrabold tracking-tight text-white sm:text-4xl lg:text-[2.75rem]">
                    {{ $article->title }}
                </h1>

                <div class="mt-8 flex flex-wrap items-center gap-x-5 gap-y-3 text-sm text-brand-200">
                    <div class="flex items-center gap-3">
                        <x-avatar :name="$article->user?->name" size="md" tone="dark" />
                        <div class="leading-tight">
                            <p class="font-semibold text-white">{{ $article->user?->name ?? 'Anonim' }}</p>
                            <p class="text-xs">{{ $article->user?->profession ?? 'Penulis · '.config('app.name') }}</p>
                        </div>
                    </div>
                    <span class="hidden h-8 w-px bg-white/15 sm:block"></span>
                    <span class="inline-flex items-center gap-1.5">
                        <x-icon name="calendar" size="size-4" class="text-brand-300" />
                        {{ $article->created_at->translatedFormat('d F Y') }}
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <x-icon name="clock" size="size-4" class="text-brand-300" />
                        {{ $article->reading_time }} menit baca
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <x-icon name="eye" size="size-4" class="text-brand-300" />
                        {{ number_format($article->view_count, 0, ',', '.') }} kali dilihat
                    </span>
                    @if ($article->medicalReviewer)
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1 text-white">
                            <x-icon name="shield" size="size-4" class="text-brand-300" />
                            Ditinjau oleh {{ $article->medicalReviewer->name }}
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
        <aside aria-label="Simpan dan bagikan artikel" class="lg:sticky lg:top-24 lg:self-start">
            <div class="flex flex-wrap items-center gap-2 lg:flex-col lg:gap-3">
                @include('public._bookmark-button', ['class' => 'share-btn'])
                <span class="mx-1 h-6 w-px bg-gray-200 lg:mx-0 lg:h-px lg:w-6"></span>
                <span class="text-[0.75rem] font-semibold tracking-wide text-gray-500 uppercase">Bagikan</span>
                <x-share-buttons :article="$article" class="lg:flex-col lg:gap-3" />
            </div>
        </aside>

        {{-- Kolom 2: isi artikel, penulis & komentar. --}}
        <div class="min-w-0 space-y-8">
            <article class="card overflow-hidden">
                @if ($article->image_url)
                    <img src="{{ $article->image_url }}" alt="{{ $article->title }}" class="aspect-[16/9] w-full object-cover">
                @endif

                <div class="px-6 py-8 sm:px-12 sm:py-10">
                    {{-- content_html sudah disanitasi (App\Support\ArticleContent), aman untuk {!! !!}. --}}
                    <div class="article-content article-lead">{!! $article->content_html !!}</div>
                </div>

                {{-- Tinjauan medis & sumber rujukan: penanda kredibilitas informasi kesehatan. --}}
                @if ($article->medicalReviewer || $article->source_list)
                    <div class="space-y-6 border-t border-gray-100 px-6 py-6 sm:px-12">
                        @if ($article->medicalReviewer)
                            <div class="flex items-start gap-3 rounded-xl bg-brand-50 p-4">
                                <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-white text-brand-600">
                                    <x-icon name="shield" size="size-5" />
                                </span>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">Ditinjau secara medis oleh {{ $article->medicalReviewer->name }}</p>
                                    @if ($article->medicalReviewer->profession)
                                        <p class="text-sm text-gray-700">{{ $article->medicalReviewer->profession }}</p>
                                    @endif
                                    <p class="mt-1 text-xs text-gray-600">Terakhir diperbarui <x-date :value="$article->updated_at" format="d F Y" /></p>
                                </div>
                            </div>
                        @endif

                        @if ($article->source_list)
                            <section>
                                <h2 class="text-sm font-bold tracking-wider text-gray-600 uppercase">Sumber rujukan</h2>
                                <ol class="mt-3 list-decimal space-y-2 pl-5 text-sm leading-relaxed text-gray-700">
                                    @foreach ($article->source_list as $source)
                                        <li><x-source-line :line="$source" /></li>
                                    @endforeach
                                </ol>
                            </section>
                        @endif
                    </div>
                @endif

                {{-- Akhir artikel: ajakan menyimpan & membagikan. --}}
                <footer class="border-t border-gray-100 bg-gray-50/70 px-6 py-5 sm:px-12">
                    <p class="text-sm font-semibold text-gray-900">Artikel ini bermanfaat? Bagikan ke keluarga &amp; teman.</p>
                    <x-share-buttons :article="$article" labels class="mt-3 flex-wrap" />
                </footer>
            </article>

            @if ($article->user)
                <section class="card flex items-center gap-4 p-5 sm:p-6">
                    <x-avatar :name="$article->user->name" size="md" />
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-semibold tracking-wider text-gray-500 uppercase">Ditulis oleh</p>
                        <p class="font-bold text-gray-900">{{ $article->user->name }}</p>
                        <p class="text-sm text-gray-600">{{ $article->user->profession ?? 'Penulis di '.config('app.name') }}</p>
                    </div>
                    @include('public._bookmark-button', ['class' => 'btn btn-secondary btn-sm', 'withLabel' => true])
                </section>
            @endif

            {{-- Komentar --}}
            <section id="komentar" class="card scroll-mt-24 p-5 sm:p-6">
                <h2 class="flex items-center gap-2 text-lg font-bold text-gray-900">
                    <x-icon name="comments" class="text-brand-600" /> Komentar
                    <span class="rounded-full bg-gray-100 px-2 text-sm font-semibold text-gray-600">{{ $article->comments->count() }}</span>
                </h2>

                @auth
                    <form action="{{ route('comments.store', $article) }}" method="POST" class="mt-5 flex gap-3">
                        @csrf
                        <x-avatar :name="auth()->user()->name" />
                        <div class="min-w-0 flex-1">
                            <label for="body" class="sr-only">Tulis komentar</label>
                            <textarea id="body" name="body" rows="3" maxlength="2000" required
                                      placeholder="Tulis komentar atau pertanyaan Anda..."
                                      @class(['form-input', 'form-input-error' => $errors->has('body')])>{{ old('body') }}</textarea>
                            @error('body')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                            <div class="mt-3 flex justify-end">
                                <button type="submit" class="btn btn-primary btn-sm">Kirim Komentar</button>
                            </div>
                        </div>
                    </form>
                @else
                    <div class="mt-5 rounded-lg bg-brand-50 px-4 py-3 text-sm text-brand-900">
                        <a href="{{ route('login') }}" class="link">Masuk</a> atau
                        <a href="{{ route('register') }}" class="link">daftar</a> untuk ikut berkomentar.
                    </div>
                @endauth

                @if ($article->comments->isEmpty())
                    <p class="mt-6 text-sm text-gray-500">Belum ada komentar. Jadilah yang pertama berkomentar.</p>
                @else
                    <ul class="mt-6 space-y-4">
                        @foreach ($article->comments as $comment)
                            <li class="flex gap-3">
                                <x-avatar :name="$comment->user->name" />
                                <div class="min-w-0 flex-1 rounded-xl border border-gray-200 bg-white px-4 py-3">
                                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                        <p class="text-sm font-semibold text-gray-900">{{ $comment->user->name }}</p>
                                        @if ($comment->user_id === $article->user_id)
                                            <span class="badge bg-brand-50 text-brand-700">Penulis artikel</span>
                                        @elseif ($comment->user->isAdmin())
                                            <span class="badge bg-gray-100 text-gray-600">Admin</span>
                                        @endif
                                        <span class="text-xs text-gray-500">· <x-date :value="$comment->created_at" format="d M Y, H:i" /></span>

                                        @can('delete', $comment)
                                            <form action="{{ route('comments.destroy', $comment) }}" method="POST" class="ml-auto"
                                                  onsubmit="return confirm('Hapus komentar ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="Hapus komentar" class="cursor-pointer rounded-md p-2 text-gray-500 hover:bg-red-50 hover:text-red-600">
                                                    <x-icon name="trash" size="size-3" />
                                                    <span class="sr-only">Hapus komentar</span>
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

        {{-- Kolom 3: artikel lainnya, jenis artikel, ajakan konsultasi (menempel saat scroll). --}}
        <aside class="min-w-0 space-y-6">
            @if ($latestArticles->isNotEmpty())
                <section class="card p-5">
                    <h2 class="font-bold text-gray-900">Artikel Lainnya</h2>
                    <ul class="mt-2 divide-y divide-gray-100">
                        @foreach ($latestArticles as $related)
                            <li>
                                <a href="{{ route('public.articles.show', $related) }}" class="group flex gap-3 py-3">
                                    <x-article-thumb :article="$related" class="size-16" />
                                    <div class="min-w-0">
                                        <x-category-badge :article="$related" class="mb-1" />
                                        <p class="line-clamp-2 text-sm leading-snug font-semibold text-gray-900 group-hover:text-brand-700">{{ $related->title }}</p>
                                        <p class="mt-1 text-xs text-gray-500">{{ $related->created_at->translatedFormat('d M Y') }}</p>
                                    </div>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            <section class="card p-5">
                <h2 class="font-bold text-gray-900">Jenis Artikel</h2>
                <ul class="mt-3 space-y-1">
                    @foreach (\App\Models\Article::CATEGORIES as $value => $label)
                        <li>
                            <a href="{{ route('home', ['jenis' => $value]) }}" @class([
                                'flex items-center justify-between rounded-lg px-3 py-2 text-sm font-medium transition hover:bg-brand-50 hover:text-brand-800',
                                'bg-brand-50 text-brand-800' => $article->category === $value,
                                'text-gray-700' => $article->category !== $value,
                            ])>
                                <span class="flex items-center gap-2">
                                    <x-icon name="tag" size="size-3" class="text-brand-500" /> {{ $label }}
                                </span>
                                <span class="rounded-full bg-gray-100 px-2 text-xs text-gray-600">{{ $categoryCounts->get($value, 0) }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </section>

            <x-consultation-card class="lg:sticky lg:top-24"
                :title="$article->category_label ? 'Alami gejala '.Str::lower($article->category_label).'?' : 'Punya keluhan kesehatan?'" />
        </aside>
    </div>

    {{-- Artikel terkait: jenis yang sama dengan artikel yang sedang dibuka. --}}
    @if ($relatedArticles->isNotEmpty())
        <section class="mt-16">
            <div class="mb-6 flex flex-wrap items-end justify-between gap-2 border-b border-gray-200 pb-3">
                <h2 class="text-xl font-bold text-gray-900">Artikel Terkait</h2>
                <a href="{{ route('home', ['jenis' => $article->category]) }}" class="link inline-flex items-center gap-1.5 text-sm">
                    Semua artikel {{ $article->category_label }} <x-icon name="chevron-right" size="size-3" />
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
                <x-icon name="fire" class="text-orange-500" /> Artikel Terpopuler
            </h2>
            <p class="text-xs text-gray-500">Berdasarkan jumlah pembaca</p>
        </div>

        @if ($popularArticles->isEmpty())
            <div class="card px-6 py-10 text-center">
                <x-icon name="chart-line" size="size-7" class="text-gray-300" />
                <p class="mt-2 text-sm text-gray-500">
                    Belum ada artikel populer. Artikel akan muncul di sini setelah dibaca minimal
                    {{ config('clinic.popular_min_views') }} kali.
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
        // ikon tautan berganti centang selama 2 detik; label untuk pembaca layar ikut berubah.
        document.querySelectorAll('[data-copy-link]').forEach((button) => {
            const icon = button.querySelector('[data-copy-icon]');
            const done = button.querySelector('[data-copy-done]');
            const label = button.querySelector('[data-copy-label]');

            button.addEventListener('click', async () => {
                try {
                    await navigator.clipboard.writeText(button.dataset.copyLink);
                    icon.hidden = true;
                    done.hidden = false;
                    label.textContent = 'Tautan disalin';
                } catch {
                    label.textContent = 'Gagal menyalin';
                }
                setTimeout(() => {
                    icon.hidden = false;
                    done.hidden = true;
                    label.textContent = 'Salin tautan';
                }, 2000);
            });
        });
    </script>
@endpush
