@extends('layouts.app')

@section('title', $article->title)

@section('hero')
    <section class="bg-brand-950">
        <div class="container-page py-12 sm:py-16">
            <nav aria-label="Breadcrumb" class="flex flex-wrap items-center gap-2 text-xs font-medium text-brand-300">
                <a href="{{ route('home') }}" class="hover:text-white">Beranda</a>
                <span>/</span>
                <a href="{{ route('home') }}" class="hover:text-white">Berita &amp; Artikel</a>
            </nav>

            <h1 class="mt-5 max-w-4xl text-3xl leading-tight font-extrabold tracking-tight text-white sm:text-4xl lg:text-5xl">
                {{ $article->title }}
            </h1>

            <div class="mt-8 flex flex-wrap items-center gap-x-6 gap-y-3 text-sm text-brand-200">
                <div class="flex items-center gap-3">
                    <x-avatar :name="$article->user?->name" size="md" tone="dark" />
                    <div class="leading-tight">
                        <p class="font-semibold text-white">{{ $article->user?->name ?? 'Anonim' }}</p>
                        <p class="text-xs">Penulis</p>
                    </div>
                </div>
                <span class="inline-flex items-center gap-1.5">
                    <x-icon name="calendar" size="size-4" class="text-brand-300" />
                    {{ $article->created_at->translatedFormat('l, d F Y') }}
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <x-icon name="clock" size="size-4" class="text-brand-300" />
                    {{ $article->reading_time }} menit baca
                </span>
            </div>
        </div>
    </section>
@endsection

@section('content')
    <div class="mx-auto max-w-3xl">
        @if ($article->image_url)
            <img src="{{ $article->image_url }}" alt="{{ $article->title }}"
                 class="mb-10 aspect-[16/9] w-full rounded-2xl object-cover shadow-md">
        @endif

        <div class="card px-6 py-4 sm:px-10 sm:py-6">
            {{-- content_html sudah disanitasi (App\Support\ArticleContent), aman untuk {!! !!}. --}}
            <div class="article-content">{!! $article->content_html !!}</div>
        </div>

        <div class="mt-8 flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('home') }}" class="link inline-flex items-center gap-1.5 text-sm">
                <x-icon name="arrow-left" size="size-4" /> Kembali ke Berita &amp; Artikel
            </a>
            <div class="flex items-center gap-2">
                @auth
                    <form action="{{ route('bookmarks.toggle', $article) }}" method="POST">
                        @csrf
                        <button type="submit" @class([
                            'btn btn-sm',
                            'btn-primary' => $isBookmarked,
                            'btn-secondary' => ! $isBookmarked,
                        ])>
                            <x-icon :name="$isBookmarked ? 'bookmark-solid' : 'bookmark'" size="size-3" />
                            {{ $isBookmarked ? 'Tersimpan' : 'Simpan' }}
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn btn-secondary btn-sm">
                        <x-icon name="bookmark" size="size-3" /> Simpan
                    </a>
                @endauth
                <button type="button" id="copy-link" class="btn btn-secondary btn-sm" data-url="{{ url()->current() }}">
                    <x-icon name="link" size="size-3" /> <span id="copy-link-label">Salin tautan</span>
                </button>
            </div>
        </div>

        {{-- Komentar --}}
        <section id="komentar" class="mt-12 scroll-mt-24">
            <h2 class="flex items-center gap-2 text-xl font-bold text-gray-900">
                <x-icon name="comments" class="text-brand-600" /> Komentar
                <span class="text-base font-medium text-gray-400">({{ $article->comments->count() }})</span>
            </h2>

            @auth
                <form action="{{ route('comments.store', $article) }}" method="POST" class="card mt-5 p-4">
                    @csrf
                    <div class="flex gap-3">
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
                    </div>
                </form>
            @else
                <div class="card mt-5 px-5 py-4 text-sm text-gray-600">
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
                                    <span class="text-xs text-gray-400">· {{ $comment->created_at->diffForHumans() }}</span>

                                    @can('delete', $comment)
                                        <form action="{{ route('comments.destroy', $comment) }}" method="POST" class="ml-auto"
                                              onsubmit="return confirm('Hapus komentar ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Hapus komentar" class="cursor-pointer rounded-md p-1 text-gray-400 hover:bg-red-50 hover:text-red-600">
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

    @if ($relatedArticles->isNotEmpty())
        <section class="mt-16">
            <div class="mb-6 border-b border-gray-200 pb-3">
                <h2 class="text-xl font-bold text-gray-900">Artikel Lainnya</h2>
            </div>
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($relatedArticles as $related)
                    <x-article-card :article="$related" />
                @endforeach
            </div>
        </section>
    @endif
@endsection

@push('scripts')
    <script>
        // Hanya teks label yang diganti, supaya ikon di dalam tombol tidak ikut terhapus.
        document.getElementById('copy-link').addEventListener('click', async function () {
            const label = document.getElementById('copy-link-label');
            try {
                await navigator.clipboard.writeText(this.dataset.url);
                label.textContent = 'Tautan disalin ✓';
            } catch {
                label.textContent = 'Gagal menyalin';
            }
            setTimeout(() => (label.textContent = 'Salin tautan'), 2000);
        });
    </script>
@endpush
