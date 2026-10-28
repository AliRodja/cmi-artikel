@extends('layouts.app')

@use('App\Models\Article')

@section('title', 'Review Artikel')

@section('hero')
    <x-page-header title="Review Artikel" subtitle="Tinjau artikel yang diajukan penulis dan kelola semua artikel situs." />
@endsection

@section('content')
    @php
        $tabs = [
            ['label' => 'Menunggu Review', 'value' => '2', 'count' => $statusCounts->get(Article::STATUS_REVIEW, 0)],
            ['label' => 'Published', 'value' => '1', 'count' => $statusCounts->get(Article::STATUS_PUBLISHED, 0)],
            ['label' => 'Draft', 'value' => '0', 'count' => $statusCounts->get(Article::STATUS_DRAFT, 0)],
            ['label' => 'Semua', 'value' => 'semua', 'count' => $statusCounts->sum()],
        ];
        $activeStatus = in_array($status, ['0', '1', '2'], true) ? $status : 'semua';
    @endphp

    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <nav class="flex flex-wrap gap-1 rounded-lg bg-gray-100 p-1" aria-label="Filter status">
            @foreach ($tabs as $tab)
                <a href="{{ route('review.index', array_filter(['status' => $tab['value'], 'search' => $search], 'filled')) }}"
                   @class([
                       'flex items-center gap-2 rounded-md px-3 py-1.5 text-sm font-medium transition',
                       'bg-white text-gray-900 shadow-sm' => $activeStatus === $tab['value'],
                       'text-gray-600 hover:text-gray-900' => $activeStatus !== $tab['value'],
                   ])>
                    {{ $tab['label'] }}
                    <span @class([
                        'rounded-full px-1.5 text-xs',
                        'bg-violet-600 text-white' => $tab['value'] === '2' && $tab['count'] > 0,
                        'bg-gray-200/70 text-gray-600' => ! ($tab['value'] === '2' && $tab['count'] > 0),
                    ])>{{ $tab['count'] }}</span>
                </a>
            @endforeach
        </nav>

        <form action="{{ route('review.index') }}" method="GET" class="flex w-full gap-2 sm:w-auto">
            <input type="hidden" name="status" value="{{ $activeStatus }}">
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
                <span class="mx-auto flex size-14 items-center justify-center rounded-full bg-green-50 text-green-600">
                    <x-icon name="check-circle" size="size-7" />
                </span>
                @if ($activeStatus === '2' && $search === '')
                    <p class="mt-4 font-semibold text-gray-900">Antrean kosong</p>
                    <p class="mt-1 text-sm text-gray-500">Tidak ada artikel yang menunggu review.</p>
                @else
                    <p class="mt-4 font-semibold text-gray-900">Tidak ada artikel yang cocok</p>
                    <p class="mt-1 text-sm text-gray-500">Coba kata kunci atau filter lain.</p>
                @endif
            </div>
        @else
            <div class="relative overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="th">Artikel</th>
                            <th class="th">Penulis</th>
                            <th class="th">Status</th>
                            <th class="th">Diperbarui</th>
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
                                <td class="td whitespace-nowrap">
                                    @if ($article->user)
                                        <p class="font-medium text-gray-900">{{ $article->user->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $article->user->email }}</p>
                                    @else
                                        <span class="text-gray-400">Tanpa penulis</span>
                                    @endif
                                </td>
                                <td class="td"><x-status-badge :status="$article->status" /></td>
                                <td class="td whitespace-nowrap text-gray-500">{{ $article->updated_at->diffForHumans() }}</td>
                                <td class="td">
                                    <div class="flex justify-end gap-1">
                                        @if ($article->isInReview())
                                            <a href="{{ route('articles.show', $article) }}" class="btn btn-primary btn-sm">Tinjau</a>
                                        @else
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
                                        @endif
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
