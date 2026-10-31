@props(['tone' => 'light', 'compact' => false, 'subtitle' => false])

@php
    // Logo perusahaan diletakkan di public/images/logo-cmi.{svg,png,webp,jpg}.
    $logoPath = collect(['svg', 'png', 'webp', 'jpg'])
        ->map(fn ($extension) => "images/logo-cmi.{$extension}")
        ->first(fn ($path) => file_exists(public_path($path)));
@endphp

<span {{ $attributes->class('flex items-center gap-2.5') }}>
    @if ($logoPath)
        <img src="{{ asset($logoPath) }}" alt="{{ __('Logo :site', ['site' => config('app.name')]) }}" @class(['w-auto', 'h-8 sm:h-10' => $compact, 'h-10' => ! $compact])>
    @else
        <span @class([
            'flex size-9 items-center justify-center rounded-lg text-sm font-extrabold',
            'bg-brand-600 text-white' => $tone === 'light',
            'bg-white text-brand-700' => $tone === 'dark',
        ])>{{ Str::upper(Str::substr(config('app.name'), 0, 1)) }}</span>
    @endif

    {{-- compact: nama situs disembunyikan di ponsel agar logo muat di tengah navbar. --}}
    <span @class([
        'leading-tight',
        'hidden sm:flex sm:flex-col' => $compact,
        'flex flex-col' => ! $compact,
    ])>
        <span @class([
            'text-base font-bold tracking-tight',
            'text-brand-950' => $tone === 'light',
            'text-white' => $tone === 'dark',
        ])>{{ config('app.name') }}</span>

        @if ($subtitle)
            <span @class([
                'hidden text-[0.75rem] font-medium tracking-wider uppercase lg:inline',
                'text-brand-700' => $tone === 'light',
                'text-brand-200' => $tone === 'dark',
            ])>{{ __('Pusat Informasi Jantung') }}</span>
        @endif
    </span>
</span>
