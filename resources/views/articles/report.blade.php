@extends('layouts.app')

@use('Illuminate\Support\Carbon')

@section('title', 'Laporan Artikel')

@section('hero')
    <x-page-header title="Laporan Artikel"
                   subtitle="Artikel yang sudah terbit beserta penulisnya, terbaru di atas.">
        <x-slot:meta>
            {{-- Hanya muncul di kertas: kapan laporan dicetak. --}}
            <p class="hidden text-sm text-gray-600 print:block">
                {{ config('app.name') }} · dicetak {{ now()->translatedFormat('d F Y, H:i T') }}
                @if ($search !== '')
                    · pencarian "{{ $search }}"
                @endif
            </p>
        </x-slot:meta>
        <x-slot:actions>
            <button type="button" onclick="window.print()" class="btn btn-secondary print:hidden">
                <x-icon name="printer" size="size-4" /> Cetak
            </button>
        </x-slot:actions>
    </x-page-header>
@endsection

@section('content')
    @php
        $shown = $publishedTotal - $withoutAuthor;
        $summary = [
            ['label' => 'Artikel terbit', 'value' => $publishedTotal, 'hint' => 'Semua artikel berstatus Published', 'tone' => 'bg-brand-50 text-brand-700', 'icon' => 'document-text'],
            ['label' => 'Tampil di laporan', 'value' => $shown, 'hint' => 'Artikel terbit yang punya penulis', 'tone' => 'bg-green-50 text-green-700', 'icon' => 'check-circle'],
            ['label' => 'Tanpa penulis', 'value' => $withoutAuthor, 'hint' => 'Akun penulisnya sudah dihapus', 'tone' => 'bg-amber-50 text-amber-700', 'icon' => 'exclamation-triangle'],
        ];
    @endphp

    {{-- Ringkasan: satu kartu dibagi tiga, angka besar agar mudah dibaca sekilas. --}}
    <section class="card mb-6 grid divide-y divide-gray-100 sm:grid-cols-3 sm:divide-x sm:divide-y-0" aria-label="Ringkasan laporan">
        @foreach ($summary as $item)
            <div class="flex items-center gap-4 p-5">
                <span class="flex size-12 shrink-0 items-center justify-center rounded-xl {{ $item['tone'] }}">
                    <x-icon :name="$item['icon']" size="size-5" />
                </span>
                <div class="min-w-0">
                    <p class="text-sm font-medium text-gray-600">{{ $item['label'] }}</p>
                    <p class="text-3xl leading-tight font-extrabold text-gray-900 tabular-nums">{{ $item['value'] }}</p>
                    <p class="text-xs text-gray-500">{{ $item['hint'] }}</p>
                </div>
            </div>
        @endforeach
    </section>

    <div class="card overflow-hidden">
        {{-- Bilah alat: pencarian + keterangan jumlah. --}}
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-4 py-4 sm:px-5 print:hidden">
            <form action="{{ route('articles.report') }}" method="GET" role="search" class="flex w-full gap-2 sm:w-auto">
                <div class="relative w-full sm:w-80">
                    <x-icon name="magnifying-glass" size="size-4" class="pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 text-gray-500" />
                    <label for="report-search" class="sr-only">Cari artikel</label>
                    <input type="search" id="report-search" name="search" value="{{ $search }}"
                           placeholder="Cari judul, penulis, atau email..." class="form-input pl-9">
                </div>
                <button type="submit" class="btn btn-primary">Cari</button>
            </form>

            <p class="text-sm text-gray-600" aria-live="polite">
                @if ($articles->total() > 0)
                    Menampilkan <strong class="text-gray-900">{{ $articles->firstItem() }}–{{ $articles->lastItem() }}</strong>
                    dari <strong class="text-gray-900">{{ $articles->total() }}</strong> artikel
                @else
                    0 artikel
                @endif
                @if ($search !== '')
                    untuk "<span class="font-semibold text-gray-900">{{ $search }}</span>"
                    · <a href="{{ route('articles.report') }}" class="link">Hapus pencarian</a>
                @endif
            </p>
        </div>

        @if ($articles->isEmpty())
            <div class="px-6 py-14 text-center">
                <span class="mx-auto flex size-14 items-center justify-center rounded-full bg-gray-100 text-gray-500">
                    <x-icon name="document-text" size="size-6" />
                </span>
                @if ($search !== '')
                    <p class="mt-4 font-semibold text-gray-900">Tidak ada artikel untuk "{{ $search }}"</p>
                    <p class="mt-1 text-sm text-gray-500">Periksa ejaan, atau cari dengan nama penulis maupun email.</p>
                    <a href="{{ route('articles.report') }}" class="btn btn-secondary mt-5">Tampilkan semua</a>
                @else
                    <p class="mt-4 font-semibold text-gray-900">Belum ada artikel yang terbit</p>
                    <p class="mt-1 text-sm text-gray-500">Artikel akan muncul di sini setelah diterbitkan dan punya penulis.</p>
                @endif
            </div>
        @else
            {{-- Layar sedang ke atas (dan saat dicetak): tabel. relative → teks sr-only tidak membuat halaman melebar. --}}
            <div class="relative hidden overflow-x-auto md:block print:block">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="th w-12">No</th>
                            <th scope="col" class="th">Judul</th>
                            <th scope="col" class="th">Penulis</th>
                            <th scope="col" class="th">Email Penulis</th>
                            <th scope="col" class="th">Status</th>
                            <th scope="col" class="th whitespace-nowrap" aria-sort="descending">
                                <span class="inline-flex items-center gap-1.5">
                                    Tanggal Dibuat <x-icon name="arrow-down" size="size-3" class="text-brand-600" />
                                </span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($articles as $article)
                            @php($createdAt = Carbon::parse($article->created_at))
                            <tr class="align-top hover:bg-gray-50">
                                <td class="td text-gray-500 tabular-nums">{{ $articles->firstItem() + $loop->index }}</td>
                                <td class="td min-w-64 print:min-w-0">
                                    <a href="{{ route('public.articles.show', $article->slug) }}"
                                       class="font-semibold text-gray-900 hover:text-brand-700 hover:underline">
                                        <x-highlight :text="$article->title" :term="$search" />
                                    </a>
                                </td>
                                <td class="td">
                                    <div class="flex min-w-40 items-center gap-2.5 print:min-w-0">
                                        <x-avatar :name="$article->author_name" class="print:hidden" />
                                        <span class="font-medium text-gray-800"><x-highlight :text="$article->author_name" :term="$search" /></span>
                                    </div>
                                </td>
                                <td class="td">
                                    <a href="mailto:{{ $article->author_email }}" class="text-gray-600 hover:text-brand-700 hover:underline print:break-all">
                                        <x-highlight :text="$article->author_email" :term="$search" />
                                    </a>
                                </td>
                                <td class="td"><x-status-badge :status="$article->status" /></td>
                                <td class="td whitespace-nowrap">
                                    <time datetime="{{ $createdAt->toIso8601String() }}">
                                        <span class="block text-gray-800">{{ $createdAt->translatedFormat('d M Y') }}</span>
                                        <span class="block text-xs text-gray-500">pukul {{ $createdAt->format('H:i T') }}</span>
                                    </time>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Ponsel: kartu per artikel, isinya sama dengan kolom tabel. --}}
            <ol class="divide-y divide-gray-100 md:hidden print:hidden">
                @foreach ($articles as $article)
                    @php($createdAt = Carbon::parse($article->created_at))
                    <li class="space-y-2.5 px-4 py-4">
                        <div class="flex items-start justify-between gap-3">
                            <a href="{{ route('public.articles.show', $article->slug) }}"
                               class="font-semibold leading-snug text-gray-900 hover:text-brand-700">
                                <span class="text-gray-500 tabular-nums">{{ $articles->firstItem() + $loop->index }}.</span>
                                <x-highlight :text="$article->title" :term="$search" />
                            </a>
                            <x-status-badge :status="$article->status" />
                        </div>
                        <div class="flex items-center gap-2.5">
                            <x-avatar :name="$article->author_name" />
                            <div class="min-w-0 text-sm">
                                <p class="font-medium text-gray-800"><x-highlight :text="$article->author_name" :term="$search" /></p>
                                <a href="mailto:{{ $article->author_email }}" class="block truncate text-gray-600 hover:text-brand-700">
                                    <x-highlight :text="$article->author_email" :term="$search" />
                                </a>
                            </div>
                        </div>
                        <p class="flex items-center gap-1.5 text-sm text-gray-600">
                            <x-icon name="calendar" size="size-4" class="text-gray-500" />
                            <time datetime="{{ $createdAt->toIso8601String() }}">{{ $createdAt->translatedFormat('d M Y, H:i T') }}</time>
                        </p>
                    </li>
                @endforeach
            </ol>
        @endif
    </div>

    <div class="mt-6 print:hidden">{{ $articles->links() }}</div>
@endsection
