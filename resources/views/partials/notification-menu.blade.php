{{--
    Dropdown lonceng notifikasi (Popover API, sama seperti menu akun).
    $latestNotifications & $unreadNotificationCount dari View Composer navbar (AppServiceProvider).
--}}
<div id="notification-menu" popover class="dropdown-panel w-96 overflow-hidden">
    <div class="h-0.5 bg-gradient-to-r from-brand-600 via-brand-500 to-amber-400" aria-hidden="true"></div>
    <div class="flex items-center justify-between border-b border-brand-100/80 bg-brand-50/50 px-4 py-3">
        <p class="font-semibold text-brand-950">{{ __('Notifikasi') }}</p>
        @if ($unreadNotificationCount > 0)
            <form action="{{ route('notifications.read-all') }}" method="POST">
                @csrf
                <button type="submit" class="link cursor-pointer text-xs">{{ __('Tandai semua dibaca') }}</button>
            </form>
        @endif
    </div>

    @if ($latestNotifications->isEmpty())
        <div class="px-6 py-10 text-center">
            <x-icon name="bell" size="size-7" class="text-gray-300" />
            <p class="mt-2 text-sm text-gray-500">{{ __('Belum ada notifikasi.') }}</p>
        </div>
    @else
        <div class="max-h-96 space-y-1 overflow-y-auto p-2">
            @foreach ($latestNotifications as $notification)
                <x-notification-item :notification="$notification" />
            @endforeach
        </div>
    @endif

    <a href="{{ route('notifications.index') }}" class="block border-t border-gray-100 px-4 py-3 text-center text-sm font-medium text-brand-700 hover:bg-gray-50">
        {{ __('Lihat semua notifikasi') }}
    </a>
</div>
