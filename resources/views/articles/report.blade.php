@extends('layouts.app')

@section('title', 'Laporan Artikel')

@section('content')
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Laporan Artikel</h1>
            <p class="text-sm text-gray-500">Artikel Published beserta penulisnya (INNER JOIN articles × users), terbaru di atas.</p>
        </div>

        <form action="{{ route('articles.report') }}" method="GET" class="flex w-full gap-2 sm:w-auto">
            <input type="search" name="search" value="{{ $search }}" placeholder="Cari judul, penulis, email..."
                   class="form-input sm:w-72">
            <button type="submit" class="btn btn-primary">Cari</button>
            @if ($search !== '')
                <a href="{{ route('articles.report') }}" class="btn btn-secondary">Reset</a>
            @endif
        </form>
    </div>

    <p class="mb-3 text-sm text-gray-600">
        Total {{ $articles->total() }} artikel
        @if ($search !== '')
            untuk "<span class="font-medium">{{ $search }}</span>"
        @endif
    </p>

    <div class="card overflow-hidden">
        @if ($articles->isEmpty())
            <p class="px-4 py-12 text-center text-sm text-gray-500">Tidak ada artikel yang cocok.</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="th">No</th>
                            <th class="th">Judul</th>
                            <th class="th">Penulis</th>
                            <th class="th">Email Penulis</th>
                            <th class="th">Status</th>
                            <th class="th">Tanggal Dibuat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($articles as $article)
                            <tr>
                                <td class="td text-gray-500">{{ $articles->firstItem() + $loop->index }}</td>
                                <td class="td font-medium text-gray-900">
                                    <a href="{{ route('public.articles.show', $article->slug) }}" class="hover:text-brand-700">{{ $article->title }}</a>
                                </td>
                                <td class="td">{{ $article->author_name }}</td>
                                <td class="td text-gray-600">{{ $article->author_email }}</td>
                                <td class="td"><x-status-badge :status="$article->status" /></td>
                                <td class="td whitespace-nowrap text-gray-500">{{ \Illuminate\Support\Carbon::parse($article->created_at)->format('d M Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="mt-6">{{ $articles->links() }}</div>
@endsection
