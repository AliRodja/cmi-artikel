@extends('layouts.app')

@section('title', 'Artikel Saya')

@section('hero')
    <x-page-header title="Artikel Saya" subtitle="Kelola semua artikel yang Anda tulis.">
        <x-slot:actions>
            <a href="{{ route('articles.create') }}" class="btn btn-primary">
                <x-icon name="plus" size="size-4" /> Tulis Artikel
            </a>
        </x-slot:actions>
    </x-page-header>
@endsection

@section('content')
    @php
        $tabs = [
            ['label' => 'Semua', 'value' => null, 'count' => $statusCounts->sum()],
            ['label' => 'Published', 'value' => '1', 'count' => $statusCounts->get(\App\Models\Article::STATUS_PUBLISHED, 0)],
            ['label' => 'Draft', 'value' => '0', 'count' => $statusCounts->get(\App\Models\Article::STATUS_DRAFT, 0)],
        ];
        $activeStatus = in_array($status, ['0', '1'], true) ? $status : null;
    @endphp

    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <nav class="flex gap-1 rounded-lg bg-gray-100 p-1" aria-label="Filter status">
            @foreach ($tabs as $tab)
                <a href="{{ route('articles.index', array_filter(['status' => $tab['value'], 'search' => $search], 'filled')) }}"
                   @class([
                       'flex items-center gap-2 rounded-md px-3 py-1.5 text-sm font-medium transition',
                       'bg-white text-gray-900 shadow-sm' => $activeStatus === $tab['value'],
                       'text-gray-600 hover:text-gray-900' => $activeStatus !== $tab['value'],
                   ])>
                    {{ $tab['label'] }}
                    <span class="rounded-full bg-gray-200/70 px-1.5 text-xs text-gray-600">{{ $tab['count'] }}</span>
                </a>
            @endforeach
        </nav>

        <form action="{{ route('articles.index') }}" method="GET" class="flex w-full gap-2 sm:w-auto">
            @if ($activeStatus !== null)
                <input type="hidden" name="status" value="{{ $activeStatus }}">
            @endif
            <div class="relative w-full sm:w-72">
                <x-icon name="magnifying-glass" size="size-4" class="pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 text-gray-400" />
                <input type="search" name="search" value="{{ $search }}" placeholder="Cari judul..." class="form-input pl-9">
            </div>
            <button type="submit" class="btn btn-secondary">Cari</button>
        </form>
    </div>

    <div class="card overflow-hidden">
        @if ($articles->isEmpty())
            <div class="px-6 py-14 text-center">
                @if ($search !== '' || $activeStatus !== null)
                    <p class="font-semibold text-gray-900">Tidak ada artikel yang cocok</p>
                    <p class="mt-1 text-sm text-gray-500">Coba kata kunci atau filter lain.</p>
                    <a href="{{ route('articles.index') }}" class="btn btn-secondary mt-5">Reset filter</a>
                @else
                    <p class="font-semibold text-gray-900">Belum ada artikel</p>
                    <p class="mt-1 text-sm text-gray-500">Artikel yang Anda tulis akan muncul di sini.</p>
                    <a href="{{ route('articles.create') }}" class="btn btn-primary mt-5">
                        <x-icon name="plus" size="size-4" /> Tulis Artikel Pertama
                    </a>
                @endif
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="th">Artikel</th>
                            <th class="th">Status</th>
                            <th class="th">Dibuat</th>
                            <th class="th text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($articles as $article)
                            <tr class="hover:bg-gray-50">
                                <td class="td">
                                    <div class="flex min-w-64 items-center gap-3">
                                        <x-article-thumb :article="$article" class="h-11 w-16" />
                                        <div class="min-w-0">
                                            <a href="{{ route('articles.show', $article) }}" class="line-clamp-1 font-semibold text-gray-900 hover:text-brand-700">{{ $article->title }}</a>
                                            <p class="line-clamp-1 text-xs text-gray-500">{{ $article->excerpt }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="td"><x-status-badge :status="$article->status" /></td>
                                <td class="td whitespace-nowrap text-gray-500">{{ $article->created_at->translatedFormat('d M Y') }}</td>
                                <td class="td">
                                    <div class="flex justify-end gap-1">
                                        @if ($article->isPublished())
                                            <a href="{{ route('public.articles.show', $article) }}" target="_blank" title="Lihat di situs publik"
                                               class="rounded-md p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-900">
                                                <x-icon name="arrow-top-right-on-square" size="size-4" />
                                                <span class="sr-only">Lihat di situs publik</span>
                                            </a>
                                        @endif
                                        <a href="{{ route('articles.edit', $article) }}" title="Edit"
                                           class="rounded-md p-2 text-gray-500 hover:bg-brand-50 hover:text-brand-700">
                                            <x-icon name="pencil-square" size="size-4" />
                                            <span class="sr-only">Edit</span>
                                        </a>
                                        <form action="{{ route('articles.destroy', $article) }}" method="POST"
                                              onsubmit="return confirm('Hapus artikel ini? Tindakan ini tidak bisa dibatalkan.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Hapus" class="cursor-pointer rounded-md p-2 text-gray-500 hover:bg-red-50 hover:text-red-600">
                                                <x-icon name="trash" size="size-4" />
                                                <span class="sr-only">Hapus</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="mt-6">{{ $articles->links() }}</div>
@endsection
