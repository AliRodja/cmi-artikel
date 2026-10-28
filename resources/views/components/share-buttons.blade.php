@props(['article'])

@php
    // Link bagikan. http_build_query() meng-encode judul & URL agar aman dipakai di query string.
    $shareUrl = route('public.articles.show', $article);
    $shareLinks = [
        [
            'label' => 'WhatsApp',
            'icon' => 'whatsapp',
            'href' => 'https://wa.me/?'.http_build_query(['text' => $article->title."\n".$shareUrl]),
            'class' => 'text-[#0E7A3F] hover:bg-[#0E7A3F]',
        ],
        [
            'label' => 'X',
            'icon' => 'x-twitter',
            'href' => 'https://x.com/intent/post?'.http_build_query(['text' => $article->title, 'url' => $shareUrl]),
            'class' => 'text-gray-900 hover:bg-gray-900',
        ],
        [
            'label' => 'Facebook',
            'icon' => 'facebook',
            'href' => 'https://www.facebook.com/sharer/sharer.php?'.http_build_query(['u' => $shareUrl]),
            'class' => 'text-[#1877F2] hover:bg-[#1877F2]',
        ],
    ];
@endphp

{{-- Tombol bagikan + salin tautan. Arah susunan (mendatar/menurun) diatur pemanggil lewat class. --}}
<div {{ $attributes->class('flex items-center gap-2') }}>
    @foreach ($shareLinks as $link)
        <a href="{{ $link['href'] }}" target="_blank" rel="noopener noreferrer" title="Bagikan ke {{ $link['label'] }}"
           class="share-btn {{ $link['class'] }} hover:text-white">
            <x-icon :name="$link['icon']" size="size-5" />
            <span class="sr-only">Bagikan ke {{ $link['label'] }}</span>
        </a>
    @endforeach

    {{-- Ditangani JS di public/show (data-copy-link). Ikon centang disembunyikan dengan atribut hidden,
         bukan class: CSS Font Awesome yang tak berlayer menimpa class "hidden" Tailwind. --}}
    <button type="button" data-copy-link="{{ $shareUrl }}" title="Salin tautan"
            class="share-btn text-gray-600 hover:bg-brand-600 hover:text-white">
        <x-icon name="link" size="size-4" data-copy-icon />
        <x-icon name="check" size="size-4" data-copy-done hidden />
        <span class="sr-only" data-copy-label>Salin tautan</span>
    </button>
</div>
