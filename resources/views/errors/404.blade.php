@extends('layouts.app')

@section('title', __('Halaman Tidak Ditemukan'))
@section('robots', 'noindex, follow')

@section('content')
    @php
        // Saran bacaan agar pengunjung tidak "buntu". Dibungkus try: halaman error tidak boleh ikut gagal.
        try {
            $suggestions = \App\Models\Article::published()->latest()->take(3)->get(\App\Models\Article::LIST_COLUMNS);
        } catch (\Throwable) {
            $suggestions = collect();
        }
    @endphp

    <x-error-page code="404" icon="magnifying-glass" :title="__('Halaman tidak ditemukan')"
                  :message="__('Alamat yang Anda buka mungkin salah ketik, atau artikelnya sudah tidak ditayangkan lagi.')">
        <a href="{{ route('home') }}" class="btn btn-primary px-5 py-2.5"><x-icon name="newspaper" size="size-4" /> {{ __('Ke Beranda') }}</a>
        <button type="button" data-dialog-open="search-panel" class="btn btn-secondary px-5 py-2.5"><x-icon name="magnifying-glass" size="size-4" /> {{ __('Cari Artikel') }}</button>

        @if ($suggestions->isNotEmpty())
            <x-slot:after>
                <div class="mt-12 text-left">
                    <x-section-title :title="__('Mungkin Anda mencari')" />
                    <ul class="divide-y divide-gray-200">
                        @foreach ($suggestions as $article)
                            <li>
                                <a href="{{ route('public.articles.show', $article) }}" class="group flex items-center gap-4 py-3">
                                    <x-article-thumb :article="$article" class="size-16" />
                                    <span class="min-w-0">
                                        <span class="block font-article font-semibold text-gray-900 group-hover:text-brand-700">{{ $article->display_title }}</span>
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </x-slot:after>
        @endif
    </x-error-page>
@endsection
