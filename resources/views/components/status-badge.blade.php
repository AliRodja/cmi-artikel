@props(['status'])

@php
    // [label, warna badge, warna titik]: class ditulis lengkap agar terbaca Tailwind.
    [$label, $badgeClass, $dotClass] = match ((int) $status) {
        \App\Models\Article::STATUS_PUBLISHED => ['Published', 'bg-green-50 text-green-700 ring-green-600/20', 'bg-green-500'],
        \App\Models\Article::STATUS_REVIEW => ['Menunggu Review', 'bg-violet-50 text-violet-700 ring-violet-600/20', 'bg-violet-500'],
        default => ['Draft', 'bg-amber-50 text-amber-700 ring-amber-600/20', 'bg-amber-500'],
    };
@endphp

<span class="badge gap-1.5 whitespace-nowrap ring-1 {{ $badgeClass }}">
    <span class="size-1.5 rounded-full {{ $dotClass }}"></span> {{ $label }}
</span>
