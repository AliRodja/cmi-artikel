@extends('layouts.app')

@section('title', $article->title)

@section('content')
    <article class="mx-auto max-w-3xl">
        <a href="{{ route('articles.index') }}" class="link text-sm">← Kembali ke Artikel Saya</a>

        <div class="mt-4 flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-3xl leading-tight font-bold text-gray-900">{{ $article->title }}</h1>
                <p class="mt-2 flex items-center gap-2 text-sm text-gray-500">
                    <x-status-badge :status="$article->status" />
                    Dibuat {{ $article->created_at->format('d M Y H:i') }}
                    · Diperbarui {{ $article->updated_at->diffForHumans() }}
                </p>
            </div>

            <div class="flex gap-2">
                @if ($article->isPublished())
                    <a href="{{ route('public.articles.show', $article) }}" class="btn btn-secondary btn-sm" target="_blank">Lihat publik</a>
                @endif
                <a href="{{ route('articles.edit', $article) }}" class="btn btn-secondary btn-sm">Edit</a>
                <form action="{{ route('articles.destroy', $article) }}" method="POST"
                      onsubmit="return confirm('Hapus artikel ini? Tindakan ini tidak bisa dibatalkan.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                </form>
            </div>
        </div>

        @if ($article->image_url)
            <img src="{{ $article->image_url }}" alt="{{ $article->title }}" class="mt-6 w-full rounded-xl object-cover">
        @endif

        <div class="card article-content mt-6 p-6 sm:p-8">{{ $article->content }}</div>
    </article>
@endsection
