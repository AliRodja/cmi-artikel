@extends('layouts.app')

@section('title', 'Baca Nanti')

@section('hero')
    <x-page-header title="Baca Nanti" subtitle="Artikel yang Anda simpan untuk dibaca kemudian.">
        @if ($articles->total() > 0)
            <x-slot:meta>
                <p class="mt-2 text-sm font-medium text-brand-700">{{ $articles->total() }} artikel tersimpan</p>
            </x-slot:meta>
        @endif
    </x-page-header>
@endsection

@section('content')
    @if ($articles->isEmpty())
        <div class="card mx-auto max-w-2xl px-6 py-14 text-center">
            <span class="mx-auto flex size-16 items-center justify-center rounded-full bg-brand-50 text-brand-600">
                <x-icon name="bookmark" size="size-7" />
            </span>
            <p class="mt-4 text-lg font-semibold text-gray-900">Belum ada artikel yang disimpan</p>
            <p class="mx-auto mt-1 max-w-md text-gray-600">
                Tekan tombol <span class="font-semibold text-gray-900">Simpan</span> di halaman artikel, lalu artikelnya bisa dibaca kembali dari sini kapan saja.
            </p>
            <a href="{{ route('home') }}" class="btn btn-primary mt-6 px-6 py-2.5">
                <x-icon name="newspaper" size="size-4" /> Jelajahi Artikel
            </a>
        </div>
    @else
        {{-- Daftar (bukan kartu besar) agar mudah dipindai; tiap artikel punya tombol Baca & Hapus berlabel. --}}
        <ul class="mx-auto max-w-4xl space-y-4">
            @foreach ($articles as $article)
                <li class="card flex flex-col gap-4 p-4 sm:flex-row sm:items-center sm:p-5">
                    <a href="{{ route('public.articles.show', $article) }}" class="shrink-0" tabindex="-1" aria-hidden="true">
                        <x-article-thumb :article="$article" class="aspect-[16/9] w-full rounded-xl sm:aspect-[4/3] sm:w-36" />
                    </a>
                    <div class="min-w-0 flex-1">
                        <h2 class="font-article text-lg leading-snug font-semibold text-gray-900">
                            <a href="{{ route('public.articles.show', $article) }}" class="hover:text-brand-700">{{ $article->title }}</a>
                        </h2>
                        <p class="mt-1 line-clamp-2 text-sm text-gray-600">{{ $article->excerpt }}</p>
                        <p class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-500">
                            <span class="inline-flex items-center gap-1.5"><x-icon name="bookmark-solid" size="size-3" class="text-brand-600" /> Disimpan <x-date :value="$article->pivot->created_at" /></span>
                        </p>
                    </div>
                    <div class="flex shrink-0 gap-2 sm:flex-col">
                        <a href="{{ route('public.articles.show', $article) }}" class="btn btn-primary flex-1 sm:flex-none">
                            <x-icon name="newspaper" size="size-4" /> Baca
                        </a>
                        {{-- toggle(): artikel yang sudah tersimpan akan dihapus dari daftar. --}}
                        <form action="{{ route('bookmarks.toggle', $article) }}" method="POST" class="flex-1 sm:flex-none">
                            @csrf
                            <button type="submit" class="btn btn-secondary w-full">
                                <x-icon name="trash" size="size-4" /> Hapus
                            </button>
                        </form>
                    </div>
                </li>
            @endforeach
        </ul>

        <div class="mx-auto mt-8 max-w-4xl">{{ $articles->links() }}</div>
    @endif
@endsection
