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
            <button type="button" id="copy-link" class="btn btn-secondary btn-sm" data-url="{{ url()->current() }}">
                <x-icon name="link" size="size-3" /> <span id="copy-link-label">Salin tautan</span>
            </button>
        </div>
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
