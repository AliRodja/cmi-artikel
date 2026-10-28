@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
            <p class="text-sm text-gray-500">Halo, {{ auth()->user()->name }}. Berikut ringkasan artikel Anda.</p>
        </div>
        <a href="{{ route('articles.create') }}" class="btn btn-primary">+ Tulis Artikel</a>
    </div>

    <div class="mb-8 grid gap-4 sm:grid-cols-3">
        <div class="card p-5">
            <p class="text-sm text-gray-500">Jumlah Artikel</p>
            <p class="mt-1 text-3xl font-bold text-gray-900">{{ $totalCount }}</p>
        </div>
        <div class="card p-5">
            <p class="text-sm text-gray-500">Published</p>
            <p class="mt-1 text-3xl font-bold text-green-600">{{ $publishedCount }}</p>
        </div>
        <div class="card p-5">
            <p class="text-sm text-gray-500">Draft</p>
            <p class="mt-1 text-3xl font-bold text-gray-600">{{ $draftCount }}</p>
        </div>
    </div>

    <div class="card overflow-hidden">
        <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3">
            <h2 class="font-semibold text-gray-900">Artikel Terbaru</h2>
            <a href="{{ route('articles.index') }}" class="link text-sm">Lihat semua</a>
        </div>

        @if ($latestArticles->isEmpty())
            <p class="px-4 py-8 text-center text-sm text-gray-500">
                Belum ada artikel. <a href="{{ route('articles.create') }}" class="link">Tulis artikel pertama Anda</a>.
            </p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="th">Judul</th>
                            <th class="th">Status</th>
                            <th class="th">Dibuat</th>
                            <th class="th"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($latestArticles as $article)
                            <tr>
                                <td class="td font-medium text-gray-900">
                                    <a href="{{ route('articles.show', $article) }}" class="hover:text-brand-700">{{ $article->title }}</a>
                                </td>
                                <td class="td"><x-status-badge :status="$article->status" /></td>
                                <td class="td whitespace-nowrap text-gray-500">{{ $article->created_at->format('d M Y') }}</td>
                                <td class="td text-right">
                                    <a href="{{ route('articles.edit', $article) }}" class="link text-sm">Edit</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
