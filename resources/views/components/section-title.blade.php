@props(['title', 'href' => null, 'linkLabel' => 'Lihat semua'])

{{-- Judul bagian beranda: garis aksen di kiri + tautan opsional di kanan. --}}
<div {{ $attributes->class('mb-5 flex items-center justify-between gap-3 border-b border-gray-200 pb-3') }}>
    <h2 class="border-l-4 border-brand-600 pl-3 text-lg leading-tight font-bold text-gray-900">{{ $title }}</h2>
    @if ($href)
        <a href="{{ $href }}" class="link inline-flex shrink-0 items-center gap-1 text-sm">
            {{ $linkLabel }} <x-icon name="chevron-right" size="size-3" />
        </a>
    @endif
</div>
