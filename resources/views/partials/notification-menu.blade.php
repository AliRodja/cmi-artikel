{{--
    Dropdown lonceng notifikasi (Popover API, sama seperti menu akun).
    $latestNotifications & $unreadNotificationCount dari View Composer navbar (AppServiceProvider).
--}}
<div id="notification-menu" popover class="dropdown-panel w-96">
    <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3">
        <p class="font-semibold text-gray-900">Notifikasi</p>
        @if ($unreadNotificationCount > 0)
            <form action="{{ route('notifications.read-all') }}" method="POST">
                @csrf
                <button type="submit" class="link cursor-pointer text-xs">Tandai semua dibaca</button>
            </form>
        @endif
    </div>

    @if ($latestNotifications->isEmpty())
        <div class="px-6 py-10 text-center">
            <x-icon name="bell" size="size-7" class="text-gray-300" />
            <p class="mt-2 text-sm text-gray-500">Belum ada notifikasi.</p>
        </div>
    @else
        <div class="max-h-96 space-y-1 overflow-y-auto p-2">
            @foreach ($latestNotifications as $notification)
                <x-notification-item :notification="$notification" />
            @endforeach
        </div>
    @endif

    <a href="{{ route('notifications.index') }}" class="block border-t border-gray-100 px-4 py-3 text-center text-sm font-medium text-brand-700 hover:bg-gray-50">
        Lihat semua notifikasi
    </a>
</div>
