@props(['name', 'size' => 'size-5'])

@php
    // Ikon Font Awesome Free 7 (CSS dimuat di resources/js/app.js). View cukup memakai nama
    // yang stabil (<x-icon name="trash" />); class "fa-..." hanya ditulis di file ini, sehingga
    // mengganti set ikon nanti cukup di satu tempat. Gaya regular (garis) dipakai bila
    // tersedia gratis, agar senada; sisanya solid.
    $icons = [
        'document-text' => 'fa-regular fa-file-lines',
        'check-circle' => 'fa-regular fa-circle-check',
        'pencil-square' => 'fa-regular fa-pen-to-square',
        'plus' => 'fa-solid fa-plus',
        'eye' => 'fa-regular fa-eye',
        'trash' => 'fa-regular fa-trash-can',
        'magnifying-glass' => 'fa-solid fa-magnifying-glass',
        'photo' => 'fa-regular fa-image',
        'arrow-top-right-on-square' => 'fa-solid fa-arrow-up-right-from-square',
        'chart-bar' => 'fa-regular fa-chart-bar',
        'arrow-left' => 'fa-solid fa-arrow-left',
        'globe-alt' => 'fa-solid fa-globe',
        'clock' => 'fa-regular fa-clock',
        'calendar' => 'fa-regular fa-calendar',
        'link' => 'fa-solid fa-link',
        'user' => 'fa-regular fa-user',
        'bars' => 'fa-solid fa-bars',
        'newspaper' => 'fa-regular fa-newspaper',
        'gauge' => 'fa-solid fa-gauge',
        'logout' => 'fa-solid fa-right-from-bracket',
        'login' => 'fa-solid fa-right-to-bracket',
        'user-plus' => 'fa-solid fa-user-plus',
        'users' => 'fa-solid fa-users',
        'bookmark' => 'fa-regular fa-bookmark',
        'chevron-down' => 'fa-solid fa-chevron-down',
        'comment' => 'fa-regular fa-comment',
        'comments' => 'fa-regular fa-comments',
        'bell' => 'fa-regular fa-bell',
        'bookmark-solid' => 'fa-solid fa-bookmark',
        'check' => 'fa-solid fa-check',
        'exclamation-triangle' => 'fa-solid fa-triangle-exclamation',
        'cloud-arrow-up' => 'fa-solid fa-cloud-arrow-up',
        'x-mark' => 'fa-solid fa-xmark',
        'tag' => 'fa-solid fa-tag',
        'chevron-right' => 'fa-solid fa-chevron-right',
        'share' => 'fa-solid fa-share-nodes',
        'stethoscope' => 'fa-solid fa-stethoscope',
        'fire' => 'fa-solid fa-fire',
        'info' => 'fa-solid fa-circle-info',
        'envelope' => 'fa-regular fa-envelope',
        'lock' => 'fa-solid fa-lock',
        'eye-slash' => 'fa-regular fa-eye-slash',
        'shield' => 'fa-solid fa-shield-halved',
        'paper-plane' => 'fa-regular fa-paper-plane',
        'envelope-open' => 'fa-regular fa-envelope-open',
        'key' => 'fa-solid fa-key',
        'rotate' => 'fa-solid fa-rotate-right',
        'map-pin' => 'fa-solid fa-location-dot',
        'chart-line' => 'fa-solid fa-chart-line',
        // Logo merek (gaya brands).
        'whatsapp' => 'fa-brands fa-whatsapp',
        'x-twitter' => 'fa-brands fa-x-twitter',
        'facebook' => 'fa-brands fa-facebook-f',
    ];

    // Ikon FA adalah huruf (font), jadi ukurannya diatur lewat font-size. CSS Font Awesome
    // tidak berlayer sehingga menimpa width dari utilitas size-* Tailwind.
    // size-3 & size-4 memakai nilai tetap: token text-xs/text-sm sudah dinaikkan untuk teks, ikon tidak perlu ikut.
    $fontSizes = [
        'size-3' => 'text-[0.75rem]',
        'size-4' => 'text-[0.875rem]',
        'size-5' => 'text-base',
        'size-6' => 'text-xl',
        'size-7' => 'text-2xl',
        'size-8' => 'text-[1.75rem]',
    ];
@endphp

<i {{ $attributes->class([$icons[$name], $fontSizes[$size] ?? 'text-base', 'shrink-0']) }} aria-hidden="true"></i>
