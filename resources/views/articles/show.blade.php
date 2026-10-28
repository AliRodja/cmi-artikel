@extends('layouts.app')

@section('title', $article->title)

@php
    $isOwner = $article->user_id === auth()->id();
@endphp

@section('hero')
    <x-page-header :title="$article->title"
                   :back-url="$isOwner ? route('articles.index') : route('review.index')"
                   :back-label="$isOwner ? 'Artikel Saya' : 'Review Artikel'">
        <x-slot:meta>
            <p class="mt-3 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-gray-500">
                <x-status-badge :status="$article->status" />
                @unless ($isOwner)
                    <span>Oleh <span class="font-medium text-gray-700">{{ $article->user?->name ?? 'Tanpa penulis' }}</span></span>
                    <span>·</span>
                @endunless
                <span>Dibuat {{ $article->created_at->translatedFormat('d F Y, H:i') }}</span>
                <span>·</span>
                <span>Diperbarui {{ $article->updated_at->diffForHumans() }}</span>
                <span>·</span>
                <span>{{ $article->reading_time }} menit baca</span>
            </p>
        </x-slot:meta>

        <x-slot:actions>
            @if ($article->isPublished())
                <a href="{{ route('public.articles.show', $article) }}" target="_blank" class="btn btn-secondary">
                    <x-icon name="arrow-top-right-on-square" size="size-4" /> Lihat publik
                </a>
            @endif
            @can('update', $article)
                <a href="{{ route('articles.edit', $article) }}" class="btn btn-primary">
                    <x-icon name="pencil-square" size="size-4" /> Edit
                </a>
            @endcan
            @can('delete', $article)
                <form action="{{ route('articles.destroy', $article) }}" method="POST"
                      onsubmit="return confirm('Hapus artikel ini? Tindakan ini tidak bisa dibatalkan.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <x-icon name="trash" size="size-4" /> Hapus
                    </button>
                </form>
            @endcan
        </x-slot:actions>
    </x-page-header>
@endsection

@section('content')
    <div class="mx-auto max-w-3xl">
        @can('review', $article)
            {{-- Panel keputusan admin untuk artikel yang diajukan. --}}
            <section class="card mb-6 border-violet-200 p-5">
                <h2 class="font-bold text-gray-900">Tinjau artikel ini</h2>
                <p class="mt-1 text-sm text-gray-500">Terbitkan jika sudah layak tayang, atau kembalikan ke penulis dengan catatan perbaikan.</p>

                <div class="mt-4 grid gap-4 sm:grid-cols-[auto_1fr] sm:items-start">
                    <form action="{{ route('review.publish', $article) }}" method="POST"
                          onsubmit="return confirm('Terbitkan artikel ini ke situs publik?')">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-primary w-full">
                            <x-icon name="check-circle" size="size-4" /> Terbitkan
                        </button>
                    </form>

                    <form action="{{ route('review.return', $article) }}" method="POST" class="space-y-2">
                        @csrf
                        @method('PATCH')
                        <label for="review_note" class="sr-only">Catatan untuk penulis</label>
                        <textarea id="review_note" name="review_note" rows="3" maxlength="1000" required
                                  placeholder="Catatan untuk penulis, mis. bagian yang perlu diperbaiki..."
                                  @class(['form-input', 'form-input-error' => $errors->has('review_note')])>{{ old('review_note') }}</textarea>
                        @error('review_note')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                        <button type="submit" class="btn btn-secondary">Kembalikan ke Penulis</button>
                    </form>
                </div>
            </section>
        @elseif ($article->isInReview())
            <div class="mb-6 flex gap-3 rounded-lg border border-violet-200 bg-violet-50 px-4 py-3 text-sm text-violet-800">
                <x-icon name="clock" class="shrink-0 text-violet-500" />
                <p>Artikel ini sedang <strong>menunggu review admin</strong> dan belum tampil di situs publik.</p>
            </div>
        @elseif ($article->review_note)
            <x-review-note :note="$article->review_note" class="mb-6" />
        @elseif (! $article->isPublished())
            <div class="mb-6 flex gap-3 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                <x-icon name="exclamation-triangle" class="shrink-0 text-amber-500" />
                <p>
                    Artikel ini masih <strong>Draft</strong> dan belum tampil di situs publik.
                    {{ auth()->user()->isAdmin() ? 'Ubah status menjadi Published melalui tombol Edit.' : 'Ajukan ke admin melalui tombol Edit jika sudah siap.' }}
                </p>
            </div>
        @elseif (! auth()->user()->isAdmin())
            <div class="mb-6 flex gap-3 rounded-lg border border-brand-100 bg-brand-50 px-4 py-3 text-sm text-brand-900">
                <x-icon name="globe-alt" class="shrink-0 text-brand-600" />
                <p>Artikel ini sudah tayang. Untuk mengubah atau menurunkannya, hubungi admin.</p>
            </div>
        @endcan

        @if ($article->image_url)
            <img src="{{ $article->image_url }}" alt="{{ $article->title }}" class="mb-6 aspect-[16/9] w-full rounded-2xl object-cover shadow-sm">
        @endif

        {{-- content_html sudah disanitasi (App\Support\ArticleContent), aman untuk {!! !!}. --}}
        <div class="card article-content px-6 py-4 sm:px-10 sm:py-6">{!! $article->content_html !!}</div>
    </div>
@endsection
