@props(['article', 'rank'])

{{-- Satu item "Artikel Terpopuler" (beranda & halaman detail): nomor peringkat, thumbnail, judul, jumlah dilihat. --}}
<a href="{{ route('public.articles.show', $article) }}"
   {{ $attributes->class('group card flex h-full items-center gap-4 border-t-4 border-t-brand-600 p-4 transition hover:-translate-y-0.5 hover:shadow-lg') }}>
    <span class="w-7 shrink-0 text-center font-article text-3xl font-extrabold text-brand-600/80 tabular-nums transition group-hover:text-brand-700">{{ $rank }}</span>
    <x-article-thumb :article="$article" class="size-16" />
    <div class="min-w-0">
        <p class="line-clamp-2 text-sm leading-snug font-semibold text-gray-900 group-hover:text-brand-700">{{ $article->display_title }}</p>
        <p class="mt-1 inline-flex items-center gap-1 text-xs text-gray-500">
            <x-icon name="eye" size="size-3" /> {{ trans_choice(':count kali dilihat', $article->view_count, ['count' => Number::format($article->view_count)]) }}
        </p>
    </div>
</a>
