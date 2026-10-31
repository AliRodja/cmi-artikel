{{--
    Dropdown akun di kanan navbar (Popover API). Dibuka oleh tombol avatar
    popovertarget="user-menu"; klik di luar atau Esc menutupnya otomatis (bawaan browser).
    Posisinya (tepat di bawah tombolnya) diatur di resources/js/app.js.
--}}
<div id="user-menu" popover class="dropdown-panel w-72 overflow-hidden">
    <div class="h-0.5 bg-gradient-to-r from-brand-600 via-brand-500 to-amber-400" aria-hidden="true"></div>
    <div class="flex items-center gap-3 border-b border-brand-100/80 bg-brand-50/50 px-4 py-4">
        <x-avatar :name="auth()->user()->name" size="md" />
        <div class="min-w-0 leading-tight">
            <p class="truncate font-semibold text-gray-900">{{ auth()->user()->name }}</p>
            @if (auth()->user()->profession)
                <p class="mt-0.5 truncate text-xs font-medium text-brand-700">{{ auth()->user()->profession }}</p>
            @endif
            <p class="mt-0.5 truncate text-xs text-gray-500">{{ auth()->user()->email }}</p>
            <x-role-badge :user="auth()->user()" class="mt-1.5" />
        </div>
    </div>

    <div class="space-y-1 p-2">
        {{-- Akun milik user ini sendiri. Manajemen seluruh pengguna (admin) ada di menu kiri: partials/nav-drawer. --}}
        <a href="{{ route('profile.edit') }}" @class(['drawer-link', 'drawer-link-active' => request()->routeIs('profile.*')])>
            <x-icon name="user" size="size-4" class="text-brand-700" /> {{ __('Kelola Akun') }}
        </a>
        <a href="{{ route('bookmarks.index') }}" @class(['drawer-link', 'drawer-link-active' => request()->routeIs('bookmarks.*')])>
            <x-icon name="bookmark" size="size-4" class="text-brand-700" /> {{ __('Baca Nanti') }}
        </a>
        <a href="{{ route('notifications.index') }}" @class(['drawer-link', 'drawer-link-active' => request()->routeIs('notifications.*')])>
            <x-icon name="bell" size="size-4" class="text-brand-700" />
            <span class="flex-1">{{ __('Notifikasi') }}</span>
            @if ($unreadNotificationCount > 0)
                <span class="rounded-full bg-red-600 px-2 text-xs font-semibold text-white">{{ $unreadNotificationCount }}</span>
            @endif
        </a>
    </div>

    <div class="border-t border-gray-100 p-2">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="drawer-link w-full cursor-pointer text-red-600 hover:bg-red-50 hover:text-red-700">
                <x-icon name="logout" size="size-4" /> {{ __('Keluar') }}
            </button>
        </form>
    </div>
</div>
