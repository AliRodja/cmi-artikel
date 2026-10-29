@props(['code', 'title', 'message', 'icon' => 'exclamation-triangle'])

{{-- Isi halaman error (resources/views/errors/*): ikon, kode, judul, penjelasan, tombol aksi (slot), saran (slot "after"). --}}
<div class="mx-auto max-w-2xl py-8 text-center sm:py-12">
    <span class="mx-auto flex size-20 items-center justify-center rounded-full bg-brand-50 text-brand-600">
        <x-icon :name="$icon" size="size-8" />
    </span>
    <p class="mt-6 text-sm font-semibold tracking-wider text-brand-700 uppercase">Kode {{ $code }}</p>
    <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-gray-900 sm:text-4xl">{{ $title }}</h1>
    <p class="mx-auto mt-3 max-w-lg text-base leading-relaxed text-gray-600">{{ $message }}</p>

    <div class="mt-8 flex flex-wrap justify-center gap-3">
        {{ $slot }}
    </div>

    {{ $after ?? '' }}
</div>
