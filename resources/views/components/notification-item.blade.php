@props(['notification'])

@php
    // Warna ikon sesuai jenis notifikasi (data['icon'] dari App\Notifications\*), agar mudah dibedakan sekilas.
    $icon = $notification->data['icon'] ?? 'bell';
    $tone = match ($icon) {
        'check-circle' => 'bg-green-50 text-green-700',
        'exclamation-triangle' => 'bg-amber-50 text-amber-700',
        'clock' => 'bg-violet-50 text-violet-700',
        default => 'bg-brand-50 text-brand-700',
    };
@endphp

{{-- Satu baris notifikasi. Klik → route notifications.open (tandai dibaca, lalu ke halaman tujuan). --}}
<a href="{{ route('notifications.open', $notification->id) }}"
   {{ $attributes->class([
       'flex gap-3 rounded-lg px-3 py-3 transition hover:bg-gray-50',
       'bg-brand-50/50' => $notification->unread(),
   ]) }}>
    <span class="flex size-10 shrink-0 items-center justify-center rounded-full {{ $tone }}">
        <x-icon :name="$icon" size="size-4" />
    </span>
    <span class="min-w-0 flex-1">
        <span class="block text-sm text-gray-700">{{ $notification->data['message'] ?? '' }}</span>
        <span class="mt-0.5 block text-sm font-semibold text-gray-900">{{ $notification->data['title'] ?? '' }}</span>
        <span class="mt-1 block text-xs text-gray-500"><x-date :value="$notification->created_at" format="d M Y, H:i" /></span>
    </span>
    @if ($notification->unread())
        <span class="badge h-fit shrink-0 bg-brand-600 text-white">Baru</span>
    @endif
</a>
