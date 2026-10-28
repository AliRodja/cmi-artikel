{{--
    Dropdown akun di kanan navbar (Popover API). Dibuka oleh tombol avatar
    popovertarget="user-menu"; klik di luar atau Esc menutupnya otomatis (bawaan browser).
    Posisinya (tepat di bawah tombolnya) diatur di resources/js/app.js.
--}}
<div id="user-menu" popover class="dropdown-panel w-72">
    <div class="flex items-center gap-3 border-b border-gray-100 px-4 py-4">
        <x-avatar :name="auth()->user()->name" size="md" />
        <div class="min-w-0 leading-tight">
            <p class="truncate font-semibold text-gray-900">{{ auth()->user()->name }}</p>
            <p class="truncate text-xs text-gray-500">{{ auth()->user()->email }}</p>
            <x-role-badge :user="auth()->user()" class="mt-1" />
        </div>
    </div>

    <div class="p-2">
        @if (auth()->user()->isAdmin())
            <a href="{{ route('users.index') }}" @class(['drawer-link', 'drawer-link-active' => request()->routeIs('users.*')])>
                <x-icon name="users" size="size-4" class="text-gray-400" /> Kelola Pengguna
            </a>
        @endif
        <a href="{{ route('bookmarks.index') }}" @class(['drawer-link', 'drawer-link-active' => request()->routeIs('bookmarks.*')])>
            <x-icon name="bookmark" size="size-4" class="text-gray-400" /> Baca Nanti
        </a>
        <a href="{{ route('notifications.index') }}" @class(['drawer-link', 'drawer-link-active' => request()->routeIs('notifications.*')])>
            <x-icon name="bell" size="size-4" class="text-gray-400" />
            <span class="flex-1">Notifikasi</span>
            @if ($unreadNotificationCount > 0)
                <span class="rounded-full bg-red-600 px-2 text-xs font-semibold text-white">{{ $unreadNotificationCount }}</span>
            @endif
        </a>
    </div>

    <div class="border-t border-gray-100 p-2">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="drawer-link w-full cursor-pointer text-red-600 hover:bg-red-50 hover:text-red-700">
                <x-icon name="logout" size="size-4" /> Keluar
            </button>
        </form>
    </div>
</div>
