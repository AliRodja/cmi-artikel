@extends('layouts.app')

@section('title', __('Dashboard'))

@section('hero')
    <x-page-header :title="__('Halo, :name', ['name' => auth()->user()->name])"
                   :subtitle="now()->translatedFormat('l, d F Y').' · '.__('Ringkasan artikel Anda.')">
        <x-slot:actions>
            <a href="{{ route('articles.create') }}" class="btn btn-primary">
                <x-icon name="plus" size="size-4" /> {{ __('Tulis Artikel') }}
            </a>
        </x-slot:actions>
    </x-page-header>
@endsection

@section('content')
    @php
        // Kartu statistik bisa diklik → langsung ke Artikel Saya dengan filter status yang sesuai.
        $stats = [
            ['label' => __('Jumlah Artikel'), 'value' => $totalCount, 'icon' => 'document-text', 'tone' => 'bg-brand-50 text-brand-700', 'status' => null],
            ['label' => __('Published'), 'value' => $publishedCount, 'icon' => 'check-circle', 'tone' => 'bg-green-50 text-green-700', 'status' => \App\Models\Article::STATUS_PUBLISHED],
            ['label' => __('Menunggu Review'), 'value' => $reviewCount, 'icon' => 'clock', 'tone' => 'bg-violet-50 text-violet-700', 'status' => \App\Models\Article::STATUS_REVIEW],
            ['label' => __('Draft'), 'value' => $draftCount, 'icon' => 'pencil-square', 'tone' => 'bg-amber-50 text-amber-700', 'status' => \App\Models\Article::STATUS_DRAFT],
        ];
        $publishedPercent = $totalCount > 0 ? round($publishedCount / $totalCount * 100) : 0;
    @endphp

    @if ($pendingReviewCount > 0)
        <a href="{{ route('review.index') }}" class="mb-6 flex items-center gap-3 rounded-xl border border-violet-200 bg-violet-50 px-5 py-4 text-sm text-violet-900 hover:bg-violet-100">
            <x-icon name="clock" class="shrink-0 text-violet-600" />
            <p class="flex-1"><strong>{{ trans_choice(':count artikel', $pendingReviewCount) }}</strong> {{ __('dari penulis menunggu review Anda.') }}</p>
            <span class="font-semibold">{{ __('Tinjau') }} &rarr;</span>
        </a>
    @endif

    @if ($returnedCount > 0)
        <a href="{{ route('articles.index', ['status' => \App\Models\Article::STATUS_DRAFT]) }}" class="mb-6 flex items-center gap-3 rounded-xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-900 hover:bg-amber-100">
            <x-icon name="exclamation-triangle" class="shrink-0 text-amber-500" />
            <p class="flex-1"><strong>{{ trans_choice(':count artikel', $returnedCount) }}</strong> {{ __('dikembalikan admin dengan catatan perbaikan.') }}</p>
            <span class="font-semibold">{{ __('Lihat') }} &rarr;</span>
        </a>
    @endif

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($stats as $stat)
            <a href="{{ route('articles.index', array_filter(['status' => $stat['status']], fn ($value) => $value !== null)) }}"
               class="group card flex items-center gap-4 p-5 transition hover:-translate-y-0.5 hover:shadow-md">
                <span class="flex size-12 items-center justify-center rounded-xl {{ $stat['tone'] }}">
                    <x-icon :name="$stat['icon']" size="size-6" />
                </span>
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-600">{{ $stat['label'] }}</p>
                    <p class="text-3xl font-extrabold tracking-tight text-gray-900">{{ $stat['value'] }}</p>
                </div>
                <x-icon name="chevron-right" size="size-3" class="text-gray-400 transition group-hover:text-brand-600" />
            </a>
        @endforeach
    </div>

    @if ($totalCount > 0)
        <div class="card mt-4 grid divide-y divide-gray-100 sm:grid-cols-[2fr_1fr] sm:divide-x sm:divide-y-0">
            <div class="p-5">
                <div class="flex items-center justify-between text-sm">
                    <span class="font-medium text-gray-700">{{ __('Artikel yang sudah dipublikasikan') }}</span>
                    <span class="font-semibold text-gray-900">{{ $publishedPercent }}%</span>
                </div>
                <div class="mt-3 h-2.5 overflow-hidden rounded-full bg-gray-100">
                    <div class="h-full rounded-full bg-green-500" style="width: {{ $publishedPercent }}%"></div>
                </div>
            </div>
            <div class="flex items-center gap-3 p-5">
                <span class="flex size-10 items-center justify-center rounded-full bg-brand-50 text-brand-700">
                    <x-icon name="eye" size="size-4" />
                </span>
                <div>
                    <p class="text-sm font-medium text-gray-600">{{ __('Total dibaca') }}</p>
                    <p class="text-xl font-extrabold text-gray-900">{{ trans_choice(':count kali', $totalViews, ['count' => Number::format($totalViews)]) }}</p>
                </div>
            </div>
        </div>
    @endif

    <div class="mt-8 grid gap-6 lg:grid-cols-3">
        <section class="card overflow-hidden lg:col-span-2">
            <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                <h2 class="font-bold text-gray-900">{{ __('Artikel Terbaru') }}</h2>
                @if ($latestArticles->isNotEmpty())
                    <a href="{{ route('articles.index') }}" class="link text-sm">{{ __('Lihat semua') }}</a>
                @endif
            </div>

            @if ($latestArticles->isEmpty())
                <div class="px-6 py-14 text-center">
                    <span class="mx-auto flex size-14 items-center justify-center rounded-full bg-brand-50 text-brand-600">
                        <x-icon name="pencil-square" size="size-7" />
                    </span>
                    <p class="mt-4 font-semibold text-gray-900">{{ __('Belum ada artikel') }}</p>
                    <p class="mx-auto mt-1 max-w-sm text-sm text-gray-500">
                        {{ auth()->user()->isAdmin()
                            ? __('Tulis artikel pertama Anda. Simpan sebagai Draft dulu, lalu publikasikan saat sudah siap.')
                            : __('Tulis artikel pertama Anda. Simpan sebagai Draft dulu, lalu ajukan ke admin saat sudah siap.') }}
                    </p>
                    <a href="{{ route('articles.create') }}" class="btn btn-primary mt-5">
                        <x-icon name="plus" size="size-4" /> {{ __('Tulis Artikel Pertama') }}
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
                                <p class="mt-1 flex flex-wrap items-center gap-2 text-xs text-gray-500">
                                    <x-status-badge :status="$article->status" />
                                    <span>{{ __('Diperbarui') }} <x-date :value="$article->updated_at" /></span>
                                </p>
                            </div>
                            @can('update', $article)
                                <a href="{{ route('articles.edit', $article) }}" class="btn btn-secondary btn-sm">
                                    <x-icon name="pencil-square" size="size-3" /> {{ __('Edit') }}
                                </a>
                            @endcan
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <aside class="space-y-6">
            @if ($topArticles->isNotEmpty())
                <section class="card p-5">
                    <h2 class="font-bold text-gray-900">{{ __('Paling banyak dibaca') }}</h2>
                    <ol class="mt-3 space-y-3">
                        @foreach ($topArticles as $article)
                            <li>
                                <a href="{{ route('articles.show', $article) }}" class="group flex items-start gap-3">
                                    <span class="w-5 shrink-0 text-lg leading-6 font-extrabold text-brand-400">{{ $loop->iteration }}</span>
                                    <span class="min-w-0">
                                        <span class="line-clamp-2 text-sm font-semibold text-gray-900 group-hover:text-brand-700">{{ $article->title }}</span>
                                        <span class="mt-0.5 inline-flex items-center gap-1 text-xs text-gray-500">
                                            <x-icon name="eye" size="size-3" /> {{ trans_choice(':count kali dibaca', $article->view_count, ['count' => Number::format($article->view_count)]) }}
                                        </span>
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ol>
                </section>
            @endif

            <section class="card p-5">
                <h2 class="font-bold text-gray-900">{{ __('Aksi Cepat') }}</h2>
                <nav class="mt-3 -mx-2 space-y-1 text-sm">
                    <a href="{{ route('articles.create') }}" class="flex items-center gap-3 rounded-lg px-2 py-2 text-gray-700 hover:bg-brand-50 hover:text-brand-700">
                        <x-icon name="pencil-square" class="text-gray-500" /> {{ __('Tulis artikel baru') }}
                    </a>
                    <a href="{{ route('articles.index', ['status' => \App\Models\Article::STATUS_DRAFT]) }}" class="flex items-center gap-3 rounded-lg px-2 py-2 text-gray-700 hover:bg-brand-50 hover:text-brand-700">
                        <x-icon name="clock" class="text-gray-500" /> {{ __('Lanjutkan draft') }}
                    </a>
                    @if (auth()->user()->isAdmin())
                        <a href="{{ route('review.index') }}" class="flex items-center gap-3 rounded-lg px-2 py-2 text-gray-700 hover:bg-brand-50 hover:text-brand-700">
                            <x-icon name="check-circle" class="text-gray-500" /> {{ __('Review artikel penulis') }}
                        </a>
                        <a href="{{ route('articles.report') }}" class="flex items-center gap-3 rounded-lg px-2 py-2 text-gray-700 hover:bg-brand-50 hover:text-brand-700">
                            <x-icon name="chart-bar" class="text-gray-500" /> {{ __('Laporan artikel') }}
                        </a>
                    @endif
                    <a href="{{ route('home') }}" target="_blank" class="flex items-center gap-3 rounded-lg px-2 py-2 text-gray-700 hover:bg-brand-50 hover:text-brand-700">
                        <x-icon name="globe-alt" class="text-gray-500" /> {{ __('Lihat situs publik') }}
                    </a>
                </nav>
            </section>

            <section class="rounded-xl bg-brand-600 p-5 text-white">
                <h2 class="font-bold">{{ __('Tips Menulis') }}</h2>
                <ul class="mt-3 list-disc space-y-1.5 pl-5 text-sm text-white/90">
                    <li>{{ __('Gunakan judul yang jelas dan spesifik.') }}</li>
                    <li>{{ __('Pakai subjudul agar artikel mudah dipindai.') }}</li>
                    <li>{{ __('Tambahkan gambar utama agar menarik di beranda.') }}</li>
                    <li>{{ __('Cantumkan sumber rujukan agar pembaca lebih percaya.') }}</li>
                    <li>{{ __('Minta dokter meninjau isi artikel sebelum diterbitkan.') }}</li>
                </ul>
            </section>
        </aside>
    </div>
@endsection
