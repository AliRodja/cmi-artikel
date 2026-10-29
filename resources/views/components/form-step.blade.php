@props(['number', 'title', 'subtitle' => null])

{{-- Judul langkah bernomor di form tulis/edit artikel (① Judul & Jenis, ② Isi, ...). --}}
<div {{ $attributes->class('mb-5 flex items-start gap-3') }}>
    <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-brand-600 text-sm font-bold text-white">{{ $number }}</span>
    <div>
        <h2 class="text-lg leading-tight font-bold text-gray-900">{{ $title }}</h2>
        @if ($subtitle)
            <p class="mt-0.5 text-sm text-gray-600">{{ $subtitle }}</p>
        @endif
    </div>
</div>
