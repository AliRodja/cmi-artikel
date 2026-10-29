@extends('layouts.app')

@section('title', 'Laporan Artikel')

@section('hero')
    <x-page-header title="Laporan Artikel"
                   subtitle="Artikel Published beserta penulisnya (INNER JOIN articles × users), terbaru di atas.">
        <x-slot:actions>
            <form action="{{ route('articles.report') }}" method="GET" class="flex w-full gap-2 sm:w-auto">
                <div class="relative w-full sm:w-72">
                    <x-icon name="magnifying-glass" size="size-4" class="pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 text-gray-500" />
                    <input type="search" name="search" value="{{ $search }}" placeholder="Cari judul, penulis, email..."
                           class="form-input pl-9">
                </div>
                <button type="submit" class="btn btn-primary">Cari</button>
                @if ($search !== '')
                    <a href="{{ route('articles.report') }}" class="btn btn-secondary">Reset</a>
                @endif
            </form>
        </x-slot:actions>
    </x-page-header>
@endsection

@section('content')
    @php
        $summary = [
            ['label' => 'Published seluruhnya', 'value' => $publishedTotal, 'hint' => 'status = 1', 'tone' => 'bg-brand-50 text-brand-700', 'icon' => 'document-text'],
            ['label' => 'Tampil di laporan', 'value' => $publishedTotal - $withoutAuthor, 'hint' => 'punya penulis (INNER JOIN)', 'tone' => 'bg-green-50 text-green-700', 'icon' => 'check-circle'],
            ['label' => 'Tersaring INNER JOIN', 'value' => $withoutAuthor, 'hint' => 'tanpa penulis (user_id NULL)', 'tone' => 'bg-amber-50 text-amber-700', 'icon' => 'exclamation-triangle'],
        ];
    @endphp

    <div class="mb-6 grid gap-4 sm:grid-cols-3">
        @foreach ($summary as $item)
            <div class="card flex items-center gap-4 p-5">
                <span class="flex size-12 shrink-0 items-center justify-center rounded-xl {{ $item['tone'] }}">
                    <x-icon :name="$item['icon']" size="size-5" />
                </span>
                <div>
                    <p class="text-sm font-medium text-gray-600">{{ $item['label'] }}</p>
                    <p class="text-2xl font-extrabold text-gray-900">{{ $item['value'] }}</p>
                    <p class="text-xs text-gray-500">{{ $item['hint'] }}</p>
                </div>
            </div>
        @endforeach
    </div>

    <p class="mb-3 text-sm text-gray-600">
        @if ($search !== '')
            {{ $articles->total() }} artikel untuk "<span class="font-semibold text-gray-900">{{ $search }}</span>"
            · <a href="{{ route('articles.report') }}" class="link">Hapus pencarian</a>
        @else
            Menampilkan {{ $articles->total() }} artikel Published yang punya penulis, terbaru di atas, 10 per halaman.
        @endif
    </p>

    <div class="card overflow-hidden">
        @if ($articles->isEmpty())
            <p class="px-4 py-12 text-center text-gray-600">Tidak ada artikel yang cocok.</p>
        @else
            {{-- relative: teks sr-only di dalam tabel tidak membuat halaman melebar di ponsel. --}}
            <div class="relative overflow-x-auto">
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
                                <td class="td whitespace-nowrap text-gray-600">{{ \Illuminate\Support\Carbon::parse($article->created_at)->translatedFormat('d M Y, H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="mt-6">{{ $articles->links() }}</div>
@endsection
