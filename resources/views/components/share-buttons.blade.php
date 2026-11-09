@props(['article', 'labels' => false])

@php
    // Link bagikan. http_build_query() meng-encode judul & URL agar aman dipakai di query string.
    $shareUrl = route('public.articles.show', $article);
    $shareLinks = [
        [
            'label' => 'WhatsApp',
            'icon' => 'whatsapp',
            'href' => 'https://wa.me/?'.http_build_query(['text' => $article->display_title."\n".$shareUrl]),
            'class' => 'text-whatsapp hover:bg-whatsapp',
        ],
        [
            'label' => 'X',
            'icon' => 'x-twitter',
            'href' => 'https://x.com/intent/post?'.http_build_query(['text' => $article->display_title, 'url' => $shareUrl]),
            'class' => 'text-gray-900 hover:bg-gray-900',
        ],
        [
            'label' => 'Facebook',
            'icon' => 'facebook',
            'href' => 'https://www.facebook.com/sharer/sharer.php?'.http_build_query(['u' => $shareUrl]),
            // Biru Facebook resmi #1877F2 hanya 4,2:1 untuk teks label; versi ini 5,6:1.
            'class' => 'text-[#1464D2] hover:bg-[#1464D2]',
        ],
    ];
@endphp

{{--
    Tombol bagikan + salin tautan. Arah susunan (mendatar/menurun) diatur pemanggil lewat class.
    labels: tampilkan teks di samping ikon ("WhatsApp", "Salin tautan", ...), lebih jelas bagi pembaca usia matang.
--}}
<div {{ $attributes->class('flex items-center gap-2') }}>
    @foreach ($shareLinks as $link)
        <a href="{{ $link['href'] }}" target="_blank" rel="noopener noreferrer" title="{{ __('Bagikan ke :network', ['network' => $link['label']]) }}"
           @class(['share-btn', 'w-auto gap-2 px-4 text-sm font-semibold' => $labels, $link['class'], 'hover:text-white'])>
            <x-icon :name="$link['icon']" size="size-5" />
            <span @class(['sr-only' => ! $labels])>{{ $labels ? $link['label'] : __('Bagikan ke :network', ['network' => $link['label']]) }}</span>
        </a>
    @endforeach

    {{-- Ditangani JS di public/show (data-copy-link). Ikon centang disembunyikan dengan atribut hidden,
         bukan class: CSS Font Awesome yang tak berlayer menimpa class "hidden" Tailwind. --}}
    <button type="button" data-copy-link="{{ $shareUrl }}" title="{{ __('Salin tautan') }}"
            data-text-copy="{{ __('Salin tautan') }}" data-text-copied="{{ __('Tautan disalin') }}" data-text-failed="{{ __('Gagal menyalin') }}"
            @class(['share-btn text-gray-700 hover:bg-brand-600 hover:text-white', 'w-auto gap-2 px-4 text-sm font-semibold' => $labels])>
        <span data-copy-icon class="inline-flex items-center justify-center"><x-icon name="link" size="size-4" /></span>
        <span data-copy-done hidden class="inline-flex items-center justify-center"><x-icon name="check" size="size-4" /></span>
        <span @class(['sr-only' => ! $labels]) data-copy-label>{{ __('Salin tautan') }}</span>
    </button>
</div>
