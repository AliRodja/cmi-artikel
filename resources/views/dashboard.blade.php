@extends('layouts.app')

@section('title', 'Dashboard')

@section('hero')
    <x-page-header :title="'Halo, '.auth()->user()->name"
                   :subtitle="now()->translatedFormat('l, d F Y').' · Ringkasan artikel Anda.'">
        <x-slot:actions>
            <a href="{{ route('articles.create') }}" class="btn btn-primary">
                <x-icon name="plus" size="size-4" /> Tulis Artikel
            </a>
        </x-slot:actions>
    </x-page-header>
@endsection

@section('content')
    @php
        $stats = [
            ['label' => 'Jumlah Artikel', 'value' => $totalCount, 'icon' => 'document-text', 'tone' => 'bg-brand-50 text-brand-700'],
            ['label' => 'Published', 'value' => $publishedCount, 'icon' => 'check-circle', 'tone' => 'bg-green-50 text-green-700'],
            ['label' => 'Menunggu Review', 'value' => $reviewCount, 'icon' => 'clock', 'tone' => 'bg-violet-50 text-violet-700'],
            ['label' => 'Draft', 'value' => $draftCount, 'icon' => 'pencil-square', 'tone' => 'bg-amber-50 text-amber-700'],
        ];
        $publishedPercent = $totalCount > 0 ? round($publishedCount / $totalCount * 100) : 0;
    @endphp

    @if ($pendingReviewCount > 0)
        <a href="{{ route('review.index') }}" class="mb-6 flex items-center gap-3 rounded-xl border border-violet-200 bg-violet-50 px-5 py-4 text-sm text-violet-900 hover:bg-violet-100">
            <x-icon name="clock" class="shrink-0 text-violet-600" />
            <p class="flex-1"><strong>{{ $pendingReviewCount }} artikel</strong> dari penulis menunggu review Anda.</p>
            <span class="font-semibold">Tinjau &rarr;</span>
        </a>
    @endif

    @if ($returnedCount > 0)
        <a href="{{ route('articles.index', ['status' => \App\Models\Article::STATUS_DRAFT]) }}" class="mb-6 flex items-center gap-3 rounded-xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-900 hover:bg-amber-100">
            <x-icon name="exclamation-triangle" class="shrink-0 text-amber-500" />
            <p class="flex-1"><strong>{{ $returnedCount }} artikel</strong> dikembalikan admin dengan catatan perbaikan.</p>
            <span class="font-semibold">Lihat &rarr;</span>
        </a>
    @endif

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($stats as $stat)
            <div class="card flex items-center gap-4 p-5">
                <span class="flex size-12 items-center justify-center rounded-xl {{ $stat['tone'] }}">
                    <x-icon :name="$stat['icon']" size="size-6" />
                </span>
                <div>
                    <p class="text-sm font-medium text-gray-500">{{ $stat['label'] }}</p>
                    <p class="text-3xl font-extrabold tracking-tight text-gray-900">{{ $stat['value'] }}</p>
                </div>
            </div>
        @endforeach
    </div>

    @if ($totalCount > 0)
        <div class="card mt-4 p-5">
            <div class="flex items-center justify-between text-sm">
                <span class="font-medium text-gray-700">Artikel yang sudah dipublikasikan</span>
                <span class="font-semibold text-gray-900">{{ $publishedPercent }}%</span>
            </div>
            <div class="mt-3 h-2 overflow-hidden rounded-full bg-gray-100">
                <div class="h-full rounded-full bg-green-500" style="width: {{ $publishedPercent }}%"></div>
            </div>
        </div>
    @endif

    <div class="mt-8 grid gap-6 lg:grid-cols-3">
        <section class="card overflow-hidden lg:col-span-2">
            <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                <h2 class="font-bold text-gray-900">Artikel Terbaru</h2>
                @if ($latestArticles->isNotEmpty())
                    <a href="{{ route('articles.index') }}" class="link text-sm">Lihat semua</a>
                @endif
            </div>

            @if ($latestArticles->isEmpty())
                <div class="px-6 py-14 text-center">
                    <span class="mx-auto flex size-14 items-center justify-center rounded-full bg-brand-50 text-brand-600">
                        <x-icon name="pencil-square" size="size-7" />
                    </span>
                    <p class="mt-4 font-semibold text-gray-900">Belum ada artikel</p>
                    <p class="mx-auto mt-1 max-w-sm text-sm text-gray-500">
                        Tulis artikel pertama Anda. Simpan sebagai Draft dulu, lalu
                        {{ auth()->user()->isAdmin() ? 'publikasikan' : 'ajukan ke admin' }} saat sudah siap.
                    </p>
                    <a href="{{ route('articles.create') }}" class="btn btn-primary mt-5">
                        <x-icon name="plus" size="size-4" /> Tulis Artikel Pertama
                    </a>
                </div>
            @else
                <ul class="divide-y divide-gray-100">
                    @foreach ($latestArticles as $article)
                        <li class="flex items-center gap-4 px-5 py-3.5 hover:bg-gray-50">
                            <x-article-thumb :article="$article" class="size-12" />
                            <div class="min-w-0 flex-1">
                                <a href="{{ route('articles.show', $article) }}" class="block truncate font-semibold text-gray-900 hover:text-brand-700">
                                    {{ $article->title }}
                                </a>
                                <p class="mt-0.5 flex items-center gap-2 text-xs text-gray-500">
                                    <x-status-badge :status="$article->status" />
                                    Diperbarui {{ $article->updated_at->diffForHumans() }}
                                </p>
                            </div>
                            @can('update', $article)
                                <a href="{{ route('articles.edit', $article) }}" class="btn btn-secondary btn-sm">Edit</a>
                            @endcan
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <aside class="space-y-6">
            <section class="card p-5">
                <h2 class="font-bold text-gray-900">Aksi Cepat</h2>
                <nav class="mt-3 -mx-2 space-y-1 text-sm">
                    <a href="{{ route('articles.create') }}" class="flex items-center gap-3 rounded-lg px-2 py-2 text-gray-700 hover:bg-brand-50 hover:text-brand-700">
                        <x-icon name="pencil-square" class="text-gray-400" /> Tulis artikel baru
                    </a>
                    <a href="{{ route('articles.index', ['status' => \App\Models\Article::STATUS_DRAFT]) }}" class="flex items-center gap-3 rounded-lg px-2 py-2 text-gray-700 hover:bg-brand-50 hover:text-brand-700">
                        <x-icon name="clock" class="text-gray-400" /> Lanjutkan draft
                    </a>
                    @if (auth()->user()->isAdmin())
                        <a href="{{ route('review.index') }}" class="flex items-center gap-3 rounded-lg px-2 py-2 text-gray-700 hover:bg-brand-50 hover:text-brand-700">
                            <x-icon name="check-circle" class="text-gray-400" /> Review artikel penulis
                        </a>
                        <a href="{{ route('articles.report') }}" class="flex items-center gap-3 rounded-lg px-2 py-2 text-gray-700 hover:bg-brand-50 hover:text-brand-700">
                            <x-icon name="chart-bar" class="text-gray-400" /> Laporan artikel
                        </a>
                    @endif
                    <a href="{{ route('home') }}" target="_blank" class="flex items-center gap-3 rounded-lg px-2 py-2 text-gray-700 hover:bg-brand-50 hover:text-brand-700">
                        <x-icon name="globe-alt" class="text-gray-400" /> Lihat situs publik
                    </a>
                </nav>
            </section>

            <section class="rounded-xl bg-brand-600 p-5 text-white">
                <h2 class="font-bold">Tips Menulis</h2>
                <ul class="mt-3 list-disc space-y-1.5 pl-5 text-sm text-white/90">
                    <li>Gunakan judul yang jelas dan spesifik.</li>
                    <li>Pakai subjudul agar artikel mudah dipindai.</li>
                    <li>Tambahkan gambar utama agar menarik di beranda.</li>
                </ul>
            </section>
        </aside>
    </div>
@endsection
