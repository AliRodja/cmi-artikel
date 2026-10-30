@props(['note'])

{{-- Catatan admin saat artikel dikembalikan. Teks biasa ({{ }}), baris baru tetap dipertahankan. --}}
<div {{ $attributes->merge(['class' => 'flex gap-3 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900']) }}>
    <x-icon name="exclamation-triangle" class="shrink-0 text-amber-500" />
    <div>
        <p class="font-semibold">{{ __('Dikembalikan oleh admin dengan catatan:') }}</p>
        <p class="mt-1 whitespace-pre-line">{{ $note }}</p>
    </div>
</div>
