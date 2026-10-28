{{--
    Dropdown akun di kanan navbar (Popover API). Dibuka oleh tombol avatar
    popovertarget="user-menu"; klik di luar atau Esc menutupnya otomatis (bawaan browser).
    Posisinya (tepat di bawah avatar) diatur di resources/js/app.js.
--}}
<div id="user-menu" popover class="user-menu">
    <div class="flex items-center gap-3 border-b border-gray-100 px-4 py-4">
        <x-avatar :name="auth()->user()->name" size="md" />
        <div class="min-w-0 leading-tight">
            <p class="truncate font-semibold text-gray-900">{{ auth()->user()->name }}</p>
            <p class="truncate text-xs text-gray-500">{{ auth()->user()->email }}</p>
            <span class="badge mt-1 bg-brand-50 text-brand-700">{{ auth()->user()->role_label }}</span>
        </div>
    </div>

    <div class="p-2">
        {{-- Fitur menyusul: ditampilkan nonaktif dulu supaya pengguna tahu fiturnya akan ada. --}}
        @if (auth()->user()->isAdmin())
            <span class="drawer-link drawer-link-disabled" aria-disabled="true">
                <x-icon name="users" size="size-4" class="text-gray-300" />
                <span class="flex-1">Kelola Pengguna</span>
                <span class="badge bg-gray-100 text-gray-500">Segera</span>
            </span>
        @endif
        <span class="drawer-link drawer-link-disabled" aria-disabled="true">
            <x-icon name="bookmark" size="size-4" class="text-gray-300" />
            <span class="flex-1">Baca Nanti</span>
            <span class="badge bg-gray-100 text-gray-500">Segera</span>
        </span>
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
