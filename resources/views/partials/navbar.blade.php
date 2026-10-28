<header data-navbar class="sticky top-0 z-30 border-t-[3px] border-b border-t-brand-600 border-b-brand-100 bg-brand-50 transition-shadow data-scrolled:shadow-md data-scrolled:shadow-brand-900/5">
    {{--
        Tiga kolom: [1fr | auto | 1fr]. Kolom kiri & kanan selalu sama lebar, sehingga
        logo di kolom tengah benar-benar berada di tengah layar, apa pun isi kedua sisinya.
        data-scrolled dipasang resources/js/app.js saat halaman di-scroll → muncul bayangan tipis.
    --}}
    <nav class="container-page grid h-16 grid-cols-[1fr_auto_1fr] items-center gap-3">
        <div class="flex items-center gap-1 justify-self-start sm:gap-2">
            {{-- Semua menu ada di drawer samping (partials/nav-drawer). --}}
            <button type="button" data-dialog-open="nav-drawer" aria-controls="nav-drawer" aria-expanded="false"
                    class="nav-icon-btn -ml-2 sm:ml-0 sm:w-auto sm:gap-2 sm:px-3">
                <x-icon name="bars" size="size-5" />
                <span class="hidden text-sm font-semibold sm:inline">Menu</span>
                <span class="sr-only sm:hidden">Buka menu</span>
            </button>

            {{-- Ponsel & tablet: ikon saja. Layar lebar: tampil seperti kolom cari. Keduanya membuka partials/search-panel. --}}
            <button type="button" data-dialog-open="search-panel" aria-controls="search-panel" aria-expanded="false"
                    class="nav-icon-btn lg:hidden">
                <x-icon name="magnifying-glass" size="size-4" />
                <span class="sr-only">Cari artikel</span>
            </button>
            <button type="button" data-dialog-open="search-panel" aria-controls="search-panel" aria-expanded="false"
                    class="hidden h-10 w-60 cursor-pointer items-center gap-2.5 rounded-full border border-brand-100 bg-white px-4 text-sm text-gray-400 shadow-xs transition hover:border-brand-300 hover:text-gray-500 lg:flex">
                <x-icon name="magnifying-glass" size="size-4" class="text-brand-700" />
                <span class="flex-1 text-left">Cari artikel…</span>
                <kbd class="rounded border border-gray-200 bg-gray-50 px-1.5 font-sans text-xs text-gray-500">/</kbd>
            </button>
        </div>

        <a href="{{ route('home') }}" class="justify-self-center rounded-lg">
            <x-logo compact />
        </a>

        <div class="flex items-center gap-1 justify-self-end sm:gap-2">
            @guest
                <a href="{{ route('login') }}" class="btn btn-sm text-brand-900 hover:bg-white">
                    <x-icon name="login" size="size-3" /> Masuk
                </a>
                {{-- Di ponsel Daftar disembunyikan agar logo di tengah tidak terdesak; halaman Masuk punya link Daftar. --}}
                <a href="{{ route('register') }}" class="btn btn-primary btn-sm hidden sm:inline-flex">Daftar</a>
            @else
                {{-- popovertarget: browser sendiri yang membuka/menutup dropdown (partials/notification-menu & user-menu). --}}
                <button type="button" popovertarget="notification-menu" class="nav-icon-btn relative">
                    <x-icon name="bell" size="size-5" />
                    @if ($unreadNotificationCount > 0)
                        <span class="absolute top-1 right-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-600 px-1 text-[0.625rem] font-bold text-white ring-2 ring-brand-50">
                            {{ $unreadNotificationCount > 9 ? '9+' : $unreadNotificationCount }}
                        </span>
                    @endif
                    <span class="sr-only">Notifikasi ({{ $unreadNotificationCount }} belum dibaca)</span>
                </button>

                {{-- Cukup avatar: nama, email & peran sudah tampil di bagian atas dropdown akun. --}}
                <button type="button" popovertarget="user-menu" title="{{ auth()->user()->name }}"
                        class="-mr-1 cursor-pointer rounded-full ring-2 ring-white transition hover:ring-brand-300 focus-visible:ring-brand-500 focus-visible:outline-none">
                    <x-avatar :name="auth()->user()->name" />
                    <span class="sr-only">Menu akun {{ auth()->user()->name }}</span>
                </button>
            @endguest
        </div>
    </nav>
</header>

@include('partials.nav-drawer')
@include('partials.search-panel')
@auth
    @include('partials.notification-menu')
    @include('partials.user-menu')
@endauth
