<header class="sticky top-0 z-30 border-b border-brand-100 bg-brand-50">
    {{--
        Tiga kolom: [1fr | auto | 1fr]. Kolom kiri & kanan selalu sama lebar, sehingga
        logo di kolom tengah benar-benar berada di tengah layar, apa pun isi kedua sisinya.
    --}}
    <nav class="container-page grid grid-cols-[1fr_auto_1fr] items-center gap-3 py-3">
        <div class="flex items-center gap-1 justify-self-start">
            {{-- Semua menu ada di drawer samping (partials/nav-drawer). --}}
            <button type="button" data-dialog-open="nav-drawer" aria-controls="nav-drawer" aria-expanded="false"
                    class="-ml-2 flex size-10 cursor-pointer items-center justify-center rounded-lg text-brand-900 transition hover:bg-white/70">
                <x-icon name="bars" size="size-6" />
                <span class="sr-only">Buka menu</span>
            </button>
            <button type="button" data-dialog-open="search-panel" aria-controls="search-panel" aria-expanded="false"
                    class="flex size-10 cursor-pointer items-center justify-center rounded-lg text-brand-900 transition hover:bg-white/70">
                <x-icon name="magnifying-glass" size="size-5" />
                <span class="sr-only">Cari artikel</span>
            </button>
        </div>

        <a href="{{ route('home') }}" class="justify-self-center">
            <x-logo compact />
        </a>

        <div class="flex items-center gap-2 justify-self-end">
            @guest
                <a href="{{ route('login') }}" class="btn btn-secondary btn-sm">Masuk</a>
                {{-- Di ponsel Daftar disembunyikan agar logo di tengah tidak terdesak; halaman Masuk punya link Daftar. --}}
                <a href="{{ route('register') }}" class="btn btn-primary btn-sm hidden sm:inline-flex">Daftar</a>
            @else
                {{-- popovertarget: browser sendiri yang membuka/menutup dropdown (partials/user-menu). --}}
                <button type="button" popovertarget="user-menu"
                        class="-mr-2 flex cursor-pointer items-center gap-2 rounded-lg py-1 pr-2 pl-1 transition hover:bg-white/70 lg:pl-3">
                    <span class="hidden text-right leading-tight lg:block">
                        <span class="block text-sm font-semibold text-gray-900">{{ auth()->user()->name }}</span>
                        <span class="block text-xs text-gray-500">{{ auth()->user()->role_label }}</span>
                    </span>
                    <x-avatar :name="auth()->user()->name" />
                    <x-icon name="chevron-down" size="size-3" class="text-brand-900/60" />
                    <span class="sr-only">Menu akun</span>
                </button>
            @endguest
        </div>
    </nav>
</header>

@include('partials.nav-drawer')
@include('partials.search-panel')
@auth
    @include('partials.user-menu')
@endauth
