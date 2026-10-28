@props(['article'])

{{-- Thumbnail kecil untuk daftar artikel di area penulis. --}}
<div {{ $attributes->class('shrink-0 overflow-hidden rounded-lg bg-brand-50') }}>
    @if ($article->image_url)
        <img src="{{ $article->image_url }}" alt="" loading="lazy" class="size-full object-cover">
    @else
        <div class="flex size-full items-center justify-center bg-gradient-to-br from-brand-500 to-brand-800 text-sm font-bold text-white/70">
            {{ Str::upper(Str::substr($article->title, 0, 1)) }}
        </div>
    @endif
</div>
