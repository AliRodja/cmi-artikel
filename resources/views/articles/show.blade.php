@extends('layouts.app')

@section('title', $article->title)

@section('hero')
    <x-page-header :title="$article->title" :back-url="route('articles.index')" back-label="Artikel Saya">
        <x-slot:meta>
            <p class="mt-3 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-gray-500">
                <x-status-badge :status="$article->status" />
                <span>Dibuat {{ $article->created_at->translatedFormat('d F Y, H:i') }}</span>
                <span>·</span>
                <span>Diperbarui {{ $article->updated_at->diffForHumans() }}</span>
                <span>·</span>
                <span>{{ $article->reading_time }} menit baca</span>
            </p>
        </x-slot:meta>

        <x-slot:actions>
            @if ($article->isPublished())
                <a href="{{ route('public.articles.show', $article) }}" target="_blank" class="btn btn-secondary">
                    <x-icon name="arrow-top-right-on-square" size="size-4" /> Lihat publik
                </a>
            @endif
            <a href="{{ route('articles.edit', $article) }}" class="btn btn-primary">
                <x-icon name="pencil-square" size="size-4" /> Edit
            </a>
            <form action="{{ route('articles.destroy', $article) }}" method="POST"
                  onsubmit="return confirm('Hapus artikel ini? Tindakan ini tidak bisa dibatalkan.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <x-icon name="trash" size="size-4" /> Hapus
                </button>
            </form>
        </x-slot:actions>
    </x-page-header>
@endsection

@section('content')
    <div class="mx-auto max-w-3xl">
        @unless ($article->isPublished())
            <div class="mb-6 flex gap-3 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                <x-icon name="exclamation-triangle" class="text-amber-500" />
                <p>Artikel ini masih <strong>Draft</strong> dan belum tampil di situs publik. Ubah status menjadi Published melalui tombol Edit.</p>
            </div>
        @endunless

        @if ($article->image_url)
            <img src="{{ $article->image_url }}" alt="{{ $article->title }}" class="mb-6 aspect-[16/9] w-full rounded-2xl object-cover shadow-sm">
        @endif

        {{-- content_html sudah disanitasi (App\Support\ArticleContent), aman untuk {!! !!}. --}}
        <div class="card article-content px-6 py-4 sm:px-10 sm:py-6">{!! $article->content_html !!}</div>
    </div>
@endsection
