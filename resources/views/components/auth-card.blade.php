@props(['title', 'subtitle' => null])

<div class="mx-auto w-full max-w-md">
    <div class="card p-6 sm:p-8">
        <h1 class="text-xl font-semibold text-gray-900">{{ $title }}</h1>
        @if ($subtitle)
            <p class="mt-1 text-sm text-gray-500">{{ $subtitle }}</p>
        @endif

        <div class="mt-6">
            {{ $slot }}
        </div>
    </div>

    @isset($footer)
        <p class="mt-4 text-center text-sm text-gray-600">{{ $footer }}</p>
    @endisset
</div>