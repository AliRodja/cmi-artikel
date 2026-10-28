@props(['title', 'subtitle' => null, 'backUrl' => null, 'backLabel' => null])

{{-- Header halaman area penulis. Dipakai di @section('hero') agar membentang selebar layar. --}}
<section class="border-b border-gray-200 bg-white">
    <div class="container-page flex flex-wrap items-end justify-between gap-4 py-8">
        <div class="min-w-0">
            @if ($backUrl)
                <a href="{{ $backUrl }}" class="mb-3 inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-brand-700">
                    <x-icon name="arrow-left" size="size-4" /> {{ $backLabel }}
                </a>
            @endif
            <h1 class="text-2xl font-extrabold tracking-tight text-gray-900 sm:text-3xl">{{ $title }}</h1>
            @if ($subtitle)
                <p class="mt-1 text-sm text-gray-500">{{ $subtitle }}</p>
            @endif
            {{ $meta ?? '' }}
        </div>

        @isset($actions)
            <div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>
        @endisset
    </div>
</section>
