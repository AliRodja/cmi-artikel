@extends('layouts.app')

@section('title', 'Baca Nanti')

@section('hero')
    <x-page-header title="Baca Nanti" subtitle="Artikel yang Anda simpan untuk dibaca kemudian." />
@endsection

@section('content')
    @if ($articles->isEmpty())
        <div class="card px-6 py-14 text-center">
            <span class="mx-auto flex size-14 items-center justify-center rounded-full bg-brand-50 text-brand-600">
                <x-icon name="bookmark" size="size-7" />
            </span>
            <p class="mt-4 font-semibold text-gray-900">Belum ada artikel yang disimpan</p>
            <p class="mx-auto mt-1 max-w-sm text-sm text-gray-500">Tekan tombol <strong>Simpan</strong> di halaman artikel untuk menyimpannya di sini.</p>
            <a href="{{ route('home') }}" class="btn btn-primary mt-5">Jelajahi Artikel</a>
        </div>
    @else
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($articles as $article)
                <x-article-card :article="$article" />
            @endforeach
        </div>

        <div class="mt-8">{{ $articles->links() }}</div>
    @endif
@endsection
