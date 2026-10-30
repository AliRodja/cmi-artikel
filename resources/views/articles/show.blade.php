@extends('layouts.app')

@section('title', $article->title)

@php
    $isOwner = $article->user_id === auth()->id();
    $publicUrl = route('public.articles.show', $article);

    // Kelengkapan: aturan sama dengan kartu Kelengkapan di form tulis (resources/js/article-form.js),
    // ditambah peninjau medis yang dipilih admin. Membantu admin memutuskan sebelum menerbitkan.
    $checks = [
        ['label' => __('Judul jelas (minimal 10 karakter)'), 'pass' => mb_strlen($article->title) >= 10],
        ['label' => __('Isi minimal 300 kata'), 'pass' => $article->word_count >= 300],
        ['label' => __('Gambar utama'), 'pass' => (bool) $article->image],
        ['label' => __('Sumber rujukan'), 'pass' => $article->source_list !== []],
        ['label' => __('Kata kunci SEO'), 'pass' => filled($article->focus_keyword)],
        ['label' => __('Ditinjau tenaga medis'), 'pass' => (bool) $article->medicalReviewer],
    ];
    $passed = collect($checks)->where('pass', true)->count();
@endphp

@section('hero')
    <x-page-header :title="$article->title"
                   :back-url="$isOwner ? route('articles.index') : route('review.index')"
                   :back-label="$isOwner ? __('Artikel Saya') : __('Review Artikel')">
        <x-slot:meta>
            <p class="mt-3 flex flex-wrap items-center gap-x-3 gap-y-2 text-sm text-gray-600">
                <x-status-badge :status="$article->status" />
                <span class="inline-flex items-center gap-1.5">
                    <x-icon name="clock" size="size-4" class="text-gray-500" />
                    {{ __('Diperbarui') }} <x-date :value="$article->updated_at" format="d F Y, H:i" />
                </span>
            </p>
        </x-slot:meta>

        <x-slot:actions>
            @if ($article->isPublished())
                <a href="{{ $publicUrl }}" target="_blank" class="btn btn-secondary">
                    <x-icon name="arrow-top-right-on-square" size="size-4" /> {{ __('Lihat di situs') }}
                </a>
            @endif
            @can('update', $article)
                <a href="{{ route('articles.edit', $article) }}" class="btn btn-primary">
                    <x-icon name="pencil-square" size="size-4" /> {{ __('Edit') }}
                </a>
            @endcan
            @can('delete', $article)
                <form action="{{ route('articles.destroy', $article) }}" method="POST"
                      data-confirm="{{ __('Hapus artikel ini? Tindakan ini tidak bisa dibatalkan.') }}" onsubmit="return confirm(this.dataset.confirm)">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <x-icon name="trash" size="size-4" /> {{ __('Hapus') }}
                    </button>
                </form>
            @endcan
        </x-slot:actions>
    </x-page-header>
@endsection

@section('content')
    {{-- Status: kenapa artikel belum tampil / apa langkah berikutnya. Di atas grid agar di ponsel pun tampil pertama. --}}
    @can('review', $article)
        <div class="mb-6 flex gap-3 rounded-lg border border-violet-200 bg-violet-50 px-4 py-3 text-sm text-violet-900">
            <x-icon name="clock" class="shrink-0 text-violet-600" />
            <p>{{ __('Artikel ini') }} <strong>{{ __('menunggu keputusan Anda') }}</strong>. {{ __('Baca pratinjaunya, lalu terbitkan atau kembalikan lewat panel') }} <strong>{{ __('Tinjau artikel ini') }}</strong>.</p>
        </div>
    @elseif ($article->isInReview())
        <div class="mb-6 flex gap-3 rounded-lg border border-violet-200 bg-violet-50 px-4 py-3 text-sm text-violet-900">
            <x-icon name="clock" class="shrink-0 text-violet-600" />
            <p>{{ __('Artikel ini sedang') }} <strong>{{ __('menunggu review admin') }}</strong> {{ __('dan belum tampil di situs publik.') }}</p>
        </div>
    @elseif ($article->review_note)
        <x-review-note :note="$article->review_note" class="mb-6" />
    @elseif (! $article->isPublished())
        <div class="mb-6 flex gap-3 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            <x-icon name="exclamation-triangle" class="shrink-0 text-amber-600" />
            <p>
                {{ __('Artikel ini masih') }} <strong>{{ __('Draft') }}</strong> {{ __('dan belum tampil di situs publik.') }}
                {{ auth()->user()->isAdmin() ? __('Ubah status menjadi Published melalui tombol Edit.') : __('Ajukan ke admin melalui tombol Edit jika sudah siap.') }}
            </p>
        </div>
    @elseif (! auth()->user()->isAdmin())
        <div class="mb-6 flex gap-3 rounded-lg border border-brand-100 bg-brand-50 px-4 py-3 text-sm text-brand-900">
            <x-icon name="globe-alt" class="shrink-0 text-brand-600" />
            <p>{{ __('Artikel ini sudah tayang. Untuk mengubah atau menurunkannya, hubungi admin.') }}</p>
        </div>
    @endcan

    {{--
        Dua kolom: kiri pratinjau artikel, kanan panel kerja.
        Di DOM panel kerja ditulis lebih dulu agar di ponsel panel Tinjau tampil sebelum isi artikel yang panjang;
        di layar lebar pratinjau dipindah ke kiri dengan lg:order-first.
    --}}
    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_21rem] lg:items-start lg:gap-8">
        <aside class="min-w-0 space-y-6">
            @can('review', $article)
                {{-- Panel keputusan admin untuk artikel yang diajukan. --}}
                <section class="card overflow-hidden border-violet-200">
                    <div class="border-b border-violet-100 bg-violet-50 px-5 py-4">
                        <h2 class="flex items-center gap-2 font-bold text-violet-900">
                            <x-icon name="check-circle" size="size-4" /> {{ __('Tinjau artikel ini') }}
                        </h2>
                        <p class="mt-1 text-sm text-violet-800">{{ __('Terbitkan bila sudah layak tayang, atau kembalikan ke penulis dengan catatan.') }}</p>
                    </div>

                    <form action="{{ route('review.publish', $article) }}" method="POST" class="space-y-3 px-5 pt-5"
                          data-confirm="{{ __('Terbitkan artikel ini ke situs publik?') }}" onsubmit="return confirm(this.dataset.confirm)">
                        @csrf
                        @method('PATCH')
                        <div>
                            @include('articles._reviewer-select', ['selected' => old('medical_reviewer_id', $article->medical_reviewer_id)])
                        </div>
                        <button type="submit" class="btn btn-primary w-full">
                            <x-icon name="globe-alt" size="size-4" /> {{ __('Terbitkan sekarang') }}
                        </button>
                    </form>

                    <div class="flex items-center gap-3 px-5 py-4 text-xs font-semibold tracking-wider text-gray-500 uppercase">
                        <span class="h-px flex-1 bg-gray-200"></span> {{ __('atau') }} <span class="h-px flex-1 bg-gray-200"></span>
                    </div>

                    <form action="{{ route('review.return', $article) }}" method="POST" class="space-y-3 px-5 pb-5">
                        @csrf
                        @method('PATCH')
                        <label for="review_note" class="form-label">{{ __('Catatan untuk penulis') }}</label>
                        <textarea id="review_note" name="review_note" rows="4" maxlength="1000" required
                                  placeholder="{{ __('Mis. lengkapi sumber rujukan dan perjelas bagian gejala...') }}"
                                  @class(['form-input', 'form-input-error' => $errors->has('review_note')])>{{ old('review_note') }}</textarea>
                        @error('review_note')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                        <button type="submit" class="btn btn-secondary w-full">
                            <x-icon name="arrow-left" size="size-4" /> {{ __('Kembalikan ke penulis') }}
                        </button>
                    </form>
                </section>
            @endcan

            {{-- Informasi artikel. --}}
            <section class="card p-5">
                <h2 class="font-bold text-gray-900">{{ __('Informasi artikel') }}</h2>
                <dl class="mt-4 space-y-4 text-sm">
                    <div>
                        <dt class="text-gray-500">{{ __('Penulis') }}</dt>
                        <dd class="mt-1.5 flex items-center gap-2.5">
                            <x-avatar :name="$article->user?->name" />
                            <span class="min-w-0">
                                <span class="block font-semibold text-gray-900">{{ $article->user?->name ?? __('Tanpa penulis') }}</span>
                                @if ($article->user?->profession)
                                    <span class="block text-gray-600">{{ $article->user->profession }}</span>
                                @endif
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">{{ __('Ditinjau secara medis oleh') }}</dt>
                        <dd class="mt-1 font-medium text-gray-900">
                            @if ($article->medicalReviewer)
                                {{ $article->medicalReviewer->name }}
                                @if ($article->medicalReviewer->profession)
                                    <span class="block font-normal text-gray-600">{{ $article->medicalReviewer->profession }}</span>
                                @endif
                            @else
                                <span class="font-normal text-gray-600">{{ __('Belum ada') }}</span>
                            @endif
                        </dd>
                    </div>
                    <div class="grid grid-cols-2 gap-4 border-t border-gray-100 pt-4">
                        <div>
                            <dt class="text-gray-500">{{ __('Dibuat') }}</dt>
                            <dd class="mt-1 text-gray-900">{{ $article->created_at->translatedFormat('d M Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">{{ __('Diperbarui') }}</dt>
                            <dd class="mt-1 text-gray-900">{{ $article->updated_at->translatedFormat('d M Y') }}</dd>
                        </div>
                        <div class="col-span-2">
                            <dt class="text-gray-500">{{ __('Panjang isi') }}</dt>
                            <dd class="mt-1 text-gray-900">{{ __(':count kata', ['count' => Number::format($article->word_count)]) }}</dd>
                        </div>
                        <div class="col-span-2">
                            <dt class="text-gray-500">{{ __('Versi Bahasa Inggris') }}</dt>
                            <dd @class(['mt-1 font-medium', 'text-green-700' => $article->hasEnglish(), 'text-gray-600' => ! $article->hasEnglish()])>
                                {{ $article->hasEnglish() ? __('Tersedia') : __('Belum ada') }}
                            </dd>
                        </div>
                    </div>
                </dl>
            </section>

            {{-- Kelengkapan. --}}
            <section class="card p-5">
                <div class="flex items-baseline justify-between gap-3">
                    <h2 class="font-bold text-gray-900">{{ __('Kelengkapan') }}</h2>
                    <span class="text-sm font-semibold text-gray-700 tabular-nums">{{ $passed }}/{{ count($checks) }}</span>
                </div>
                <div class="mt-3 h-2 overflow-hidden rounded-full bg-gray-100">
                    <div @class([
                        'h-full rounded-full',
                        'bg-green-600' => $passed === count($checks),
                        'bg-brand-600' => $passed !== count($checks),
                    ]) style="width: {{ round($passed / count($checks) * 100) }}%"></div>
                </div>
                <ul class="mt-4 space-y-2.5 text-sm">
                    @foreach ($checks as $check)
                        <li class="flex items-center gap-2.5">
                            @if ($check['pass'])
                                <span class="flex size-5 shrink-0 items-center justify-center rounded-full bg-green-100 text-green-700">
                                    <x-icon name="check" size="size-3" />
                                </span>
                                <span class="text-gray-800">{{ $check['label'] }}</span>
                            @else
                                <span class="size-5 shrink-0 rounded-full border-2 border-gray-300"></span>
                                <span class="text-gray-600">{{ $check['label'] }} <span class="sr-only">({{ __('belum') }})</span></span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </section>

            @if ($article->isPublished() || $article->view_count > 0)
                <section class="card p-5">
                    <h2 class="flex items-center gap-2 font-bold text-gray-900">
                        <x-icon name="chart-line" size="size-4" class="text-brand-600" /> {{ __('Statistik Kunjungan') }}
                    </h2>
                    <dl class="mt-4 grid grid-cols-3 divide-x divide-gray-100 rounded-lg bg-gray-50 py-3 text-center">
                        <div>
                            <dt class="text-xs text-gray-500">{{ __('Total dilihat') }}</dt>
                            <dd class="text-xl font-extrabold text-gray-900 tabular-nums">{{ Number::format($article->view_count) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-500">{{ __('7 hari') }}</dt>
                            <dd class="text-xl font-extrabold text-gray-900 tabular-nums">{{ Number::format($weeklyViews) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-500">{{ __('Komentar') }}</dt>
                            <dd class="text-xl font-extrabold text-gray-900 tabular-nums">{{ Number::format($article->comments_count) }}</dd>
                        </div>
                    </dl>

                    <p class="mt-5 text-xs font-semibold tracking-wider text-gray-500 uppercase">{{ __('Kunjungan terbaru') }}</p>
                    @if ($recentViews->isEmpty())
                        <p class="mt-2 text-sm text-gray-600">{{ __('Belum ada yang membaca artikel ini.') }}</p>
                    @else
                        <ul class="mt-2 divide-y divide-gray-100 text-sm">
                            @foreach ($recentViews as $view)
                                <li class="flex items-center justify-between gap-3 py-2">
                                    <span class="flex min-w-0 items-center gap-2">
                                        <x-avatar :name="$view->user?->name" />
                                        <span class="truncate text-gray-700">{{ $view->user?->name ?? __('Pengunjung tamu') }}</span>
                                    </span>
                                    <x-date :value="$view->viewed_at" class="shrink-0 text-xs text-gray-500" />
                                </li>
                            @endforeach
                        </ul>
                    @endif
                    <p class="mt-4 text-xs text-gray-500">{{ __('Satu pengunjung dihitung sekali; kunjungan penulis sendiri tidak dihitung.') }}</p>
                </section>
            @endif

            {{-- Cara artikel tampil di hasil pencarian Google (Judul & Deskripsi SEO, atau judul & awal isi). --}}
            <section class="card p-5">
                <h2 class="flex items-center gap-2 font-bold text-gray-900">
                    <x-icon name="magnifying-glass" size="size-4" class="text-brand-600" /> {{ __('Tampilan di Google') }}
                </h2>
                <div class="mt-4 rounded-lg border border-gray-200 p-4">
                    <p class="truncate text-sm text-gray-700">{{ parse_url($publicUrl, PHP_URL_HOST) }} › artikel › {{ $article->slug }}</p>
                    <p class="mt-1 text-lg leading-snug text-[#1a0dab]">{{ $article->meta_title }}</p>
                    <p class="mt-1 line-clamp-3 text-sm leading-relaxed text-gray-600">{{ $article->meta_description }}</p>
                </div>
                <p class="mt-3 text-sm text-gray-600">
                    {{ __('Kata kunci utama') }}:
                    @if ($article->focus_keyword)
                        <span class="badge bg-brand-50 text-brand-700">{{ $article->focus_keyword }}</span>
                    @else
                        <span class="text-gray-500">{{ __('belum diisi') }}</span>
                    @endif
                </p>
            </section>
        </aside>

        <div class="min-w-0 space-y-6 lg:order-first">
            {{-- Pratinjau: tampilan isi seperti yang dibaca pengunjung. --}}
            <article class="card overflow-hidden">
                <div class="flex items-center justify-between gap-3 border-b border-gray-100 bg-gray-50 px-5 py-3 text-sm">
                    <span class="flex items-center gap-2 font-semibold text-gray-700">
                        <x-icon name="eye" size="size-4" class="text-gray-500" /> {{ __('Pratinjau artikel') }}
                    </span>
                    @if ($article->isPublished())
                        <a href="{{ $publicUrl }}" target="_blank" class="link inline-flex items-center gap-1.5">
                            {{ __('Buka halaman publik') }} <x-icon name="arrow-top-right-on-square" size="size-3" />
                        </a>
                    @else
                        <span class="text-gray-600">{{ __('Belum tampil di situs') }}</span>
                    @endif
                </div>

                @if ($article->image_url)
                    <img src="{{ $article->image_url }}" alt="{{ $article->title }}" class="aspect-[16/9] w-full object-cover">
                @else
                    <div class="flex items-center gap-3 border-b border-dashed border-gray-200 px-6 py-4 text-sm text-gray-600 sm:px-10">
                        <x-icon name="photo" class="text-gray-500" /> {{ __('Belum ada gambar utama.') }}
                    </div>
                @endif

                <div class="px-6 py-8 sm:px-10">
                    <h2 lang="id" class="text-2xl leading-tight font-extrabold tracking-tight text-gray-900 sm:text-3xl">{{ $article->title }}</h2>
                    <p class="mt-4 flex flex-wrap items-center gap-x-2 gap-y-1 border-b border-gray-100 pb-6 text-sm text-gray-600">
                        <span class="font-semibold text-gray-800">{{ $article->user?->name ?? __('Tanpa penulis') }}</span>
                        <span aria-hidden="true">·</span>
                        <span>{{ $article->created_at->translatedFormat('d F Y') }}</span>
                    </p>

                    {{-- content_html sudah disanitasi (App\Support\ArticleContent), aman untuk {!! !!}. --}}
                    <div lang="id" class="article-content article-lead mt-6">{!! $article->content_html !!}</div>
                </div>

                @if ($article->medicalReviewer || $article->source_list)
                    <div class="space-y-6 border-t border-gray-100 px-6 py-6 sm:px-10">
                        @include('public._credibility')
                    </div>
                @endif
            </article>

            {{-- Pratinjau terjemahan penulis, untuk dicek admin/peninjau medis sebelum terbit. --}}
            @if ($article->hasEnglish())
                <article class="card overflow-hidden">
                    <div class="flex items-center gap-2 border-b border-gray-100 bg-gray-50 px-5 py-3 text-sm font-semibold text-gray-700">
                        <x-icon name="globe" size="size-4" class="text-gray-500" /> {{ __('Pratinjau versi Bahasa Inggris') }}
                    </div>
                    <div lang="en" class="px-6 py-8 sm:px-10">
                        <h2 class="text-2xl leading-tight font-extrabold tracking-tight text-gray-900 sm:text-3xl">{{ $article->title_en }}</h2>
                        {{-- content_en_html sudah disanitasi (App\Support\ArticleContent), aman untuk {!! !!}. --}}
                        <div class="article-content mt-6 border-t border-gray-100 pt-6">{!! $article->content_en_html !!}</div>
                    </div>
                </article>
            @endif
        </div>
    </div>
@endsection
