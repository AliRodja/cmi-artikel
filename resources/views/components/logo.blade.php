@props(['tone' => 'light'])

@php
    // Logo perusahaan diletakkan di public/images/logo-cmi.{svg,png,webp,jpg}.
    $logoPath = collect(['svg', 'png', 'webp', 'jpg'])
        ->map(fn ($extension) => "images/logo-cmi.{$extension}")
        ->first(fn ($path) => file_exists(public_path($path)));
@endphp

<span {{ $attributes->class('flex items-center gap-2.5') }}>
    @if ($logoPath)
        <img src="{{ asset($logoPath) }}" alt="Logo {{ config('app.name') }}"
             @class(['h-9 w-auto', 'rounded-md bg-white p-1' => $tone === 'dark'])>
    @else
        <span @class([
            'flex size-9 items-center justify-center rounded-lg text-sm font-extrabold',
            'bg-brand-600 text-white' => $tone === 'light',
            'bg-white text-brand-700' => $tone === 'dark',
        ])>{{ Str::upper(Str::substr(config('app.name'), 0, 1)) }}</span>
    @endif

    <span @class([
        'text-base font-bold tracking-tight',
        'text-brand-950' => $tone === 'light',
        'text-white' => $tone === 'dark',
    ])>{{ config('app.name') }}</span>
</span>
