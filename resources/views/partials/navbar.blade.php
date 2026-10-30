@php
    // Area penulis/admin selalu Bahasa Indonesia (App\Http\Middleware\SetLocale), jadi pilihan bahasa tidak ditampilkan.
    $localeLocked = request()->is('dashboard', 'dashboard/*');
@endphp

<header data-navbar class="sticky top-0 z-30 border-t-[3px] border-b border-t-brand-600 border-b-brand-100 bg-brand-50 transition-shadow data-scrolled:shadow-md data-scrolled:shadow-brand-900/5">
    {{--
        Tiga kolom: [1fr | auto | 1fr]. Kolom kiri & kanan selalu sama lebar, sehingga
        logo di kolom tengah benar-benar berada di tengah layar, apa pun isi kedua sisinya.
        data-scrolled dipasang resources/js/app.js saat halaman di-scroll → muncul bayangan tipis.
    --}}
    <nav class="container-page grid h-16 grid-cols-[1fr_auto_1fr] items-center gap-2 sm:gap-3">
        <div class="flex items-center gap-1 justify-self-start sm:gap-2">
            {{-- Semua menu ada di drawer samping (partials/nav-drawer). --}}
            <button type="button" data-dialog-open="nav-drawer" aria-controls="nav-drawer" aria-expanded="false"
                    class="nav-icon-btn -ml-2 sm:ml-0 sm:w-auto sm:gap-2 sm:px-3">
                <x-icon name="bars" size="size-5" />
                <span class="hidden text-sm font-semibold sm:inline">{{ __('Menu') }}</span>
                <span class="sr-only sm:hidden">{{ __('Buka menu') }}</span>
            </button>

            {{-- Tombol (bukan kolom); panel pencarian (partials/search-panel, live search) muncul saat ditekan atau dengan "/". --}}
            <button type="button" data-dialog-open="search-panel" aria-controls="search-panel" aria-expanded="false"
                    title="{{ __('Cari artikel') }} (/)" class="nav-icon-btn sm:w-auto sm:gap-2 sm:px-3">
                <x-icon name="magnifying-glass" size="size-4" />
                <span class="hidden text-sm font-semibold sm:inline">{{ __('Cari') }}</span>
                <span class="sr-only sm:hidden">{{ __('Cari artikel') }}</span>
            </button>
        </div>

        <a href="{{ route('home') }}" class="justify-self-center rounded-lg">
            <x-logo compact />
        </a>

        <div class="flex items-center gap-1 justify-self-end sm:gap-2">
            {{-- Pilihan bahasa (partials/locale-menu). Disembunyikan di area penulis/admin yang selalu Bahasa Indonesia. --}}
            @unless ($localeLocked)
                <button type="button" popovertarget="locale-menu" title="{{ __('Ganti bahasa') }}"
                        class="nav-icon-btn sm:w-auto sm:gap-1.5 sm:px-3">
                    <x-icon name="globe" size="size-4" />
                    {{-- Di ponsel cukup ikon (seperti tombol Cari) agar logo di tengah tidak terdesak. --}}
                    <span class="hidden text-sm font-semibold sm:inline">{{ config('app.supported_locales')[app()->getLocale()]['short'] }}</span>
                    <span class="sr-only">{{ __('Ganti bahasa') }}</span>
                </button>
            @endunless

            @guest
                {{-- Hanya tombol Masuk (permintaan user); pendaftaran lewat link "Daftar akun baru" di halaman Masuk. --}}
                {{-- Di ponsel tanpa ikon & tidak terlipat, agar muat bersama tombol bahasa. Ikon dibungkus <span> karena
                     CSS Font Awesome yang tak berlayer menimpa class "hidden". --}}
                <a href="{{ route('login') }}" class="btn btn-primary btn-sm whitespace-nowrap">
                    <span class="hidden sm:inline"><x-icon name="login" size="size-3" /></span> {{ __('Masuk') }}
                </a>
            @else
                {{-- popovertarget: browser sendiri yang membuka/menutup dropdown (partials/notification-menu & user-menu). --}}
                <button type="button" popovertarget="notification-menu" class="nav-icon-btn relative">
                    <x-icon name="bell" size="size-5" />
                    @if ($unreadNotificationCount > 0)
                        <span class="absolute top-1 right-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-600 px-1 text-[0.625rem] font-bold text-white ring-2 ring-brand-50">
                            {{ $unreadNotificationCount > 9 ? '9+' : $unreadNotificationCount }}
                        </span>
                    @endif
                    <span class="sr-only">{{ __('Notifikasi (:count belum dibaca)', ['count' => $unreadNotificationCount]) }}</span>
                </button>

                {{-- Cukup avatar: nama, email & peran sudah tampil di bagian atas dropdown akun. --}}
                <button type="button" popovertarget="user-menu" title="{{ auth()->user()->name }}"
                        class="-mr-1 cursor-pointer rounded-full ring-2 ring-white transition hover:ring-brand-300 focus-visible:ring-brand-500 focus-visible:outline-none">
                    <x-avatar :name="auth()->user()->name" />
                    <span class="sr-only">{{ __('Menu akun :name', ['name' => auth()->user()->name]) }}</span>
                </button>
            @endguest
        </div>
    </nav>
</header>

@include('partials.nav-drawer')
@include('partials.search-panel')
@unless ($localeLocked)
    @include('partials.locale-menu')
@endunless
@auth
    @include('partials.notification-menu')
    @include('partials.user-menu')
@endauth
