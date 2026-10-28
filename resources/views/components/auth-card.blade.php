@props(['title', 'subtitle' => null])

<div class="mx-auto w-full max-w-md py-4">
    <div class="card overflow-hidden">
        <div class="h-1.5 bg-brand-600"></div>
        <div class="p-6 sm:p-8">
            <x-logo class="mb-6" />
            <h1 class="text-2xl font-extrabold tracking-tight text-gray-900">{{ $title }}</h1>
            @if ($subtitle)
                <p class="mt-1 text-sm text-gray-500">{{ $subtitle }}</p>
            @endif

            <div class="mt-6">
                {{ $slot }}
            </div>
        </div>
    </div>

    @isset($footer)
        <p class="mt-5 text-center text-sm text-gray-600">{{ $footer }}</p>
    @endisset
</div>
