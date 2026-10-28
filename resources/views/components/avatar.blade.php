@props(['name' => null, 'size' => 'sm', 'tone' => 'light'])

@php
    $initials = $name
        ? Str::of($name)->explode(' ')->filter()->take(2)->map(fn ($word) => Str::upper(Str::substr($word, 0, 1)))->implode('')
        : '?';
@endphp

<span {{ $attributes->class([
    'inline-flex shrink-0 items-center justify-center rounded-full font-semibold',
    'bg-brand-100 text-brand-800' => $tone === 'light',
    'bg-white/10 text-white ring-1 ring-white/20' => $tone === 'dark',
    'size-8 text-xs' => $size === 'sm',
    'size-11 text-sm' => $size === 'md',
]) }}>{{ $initials }}</span>
