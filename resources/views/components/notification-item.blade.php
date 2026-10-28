@props(['notification'])

{{-- Satu baris notifikasi. Klik → route notifications.open (tandai dibaca, lalu ke halaman tujuan). --}}
<a href="{{ route('notifications.open', $notification->id) }}"
   {{ $attributes->class([
       'flex gap-3 rounded-lg px-3 py-3 transition hover:bg-gray-50',
       'bg-brand-50/60' => $notification->unread(),
   ]) }}>
    <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-white text-brand-600 ring-1 ring-gray-200">
        <x-icon :name="$notification->data['icon'] ?? 'bell'" size="size-4" />
    </span>
    <span class="min-w-0 flex-1">
        <span class="block text-sm text-gray-700">{{ $notification->data['message'] ?? '' }}</span>
        <span class="mt-0.5 block truncate text-sm font-semibold text-gray-900">{{ $notification->data['title'] ?? '' }}</span>
        <span class="mt-1 block text-xs text-gray-500">{{ $notification->created_at->diffForHumans() }}</span>
    </span>
    @if ($notification->unread())
        <span class="mt-1.5 size-2 shrink-0 rounded-full bg-brand-600" title="Belum dibaca"></span>
    @endif
</a>
