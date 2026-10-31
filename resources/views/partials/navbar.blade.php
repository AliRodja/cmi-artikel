{{-- Bilah atas (top utility bar) biru khas CMI: Hubungi Kami (Contact Us saat English) + tautan media sosial utama.
     Responsif: di ponsel tampil 1 baris rapi (Hubungi Kami di kiri, ikon medsos di kanan); di layar >= sm tampil lengkap dengan nama akun. --}}
<div class="bg-brand-600 text-white print:hidden">
    <div class="mx-auto flex w-full max-w-[96rem] items-center justify-between gap-3 px-4 py-1.5 text-xs font-medium sm:flex-wrap sm:justify-start sm:gap-x-5 sm:gap-y-1.5 sm:px-6 sm:py-2 lg:px-8">
        <a href="#hubungi-kami" class="inline-flex items-center gap-1.5 whitespace-nowrap text-white/95 transition hover:text-white hover:underline">
            {{ __('Hubungi Kami') }}
        </a>
        <span class="hidden text-white/50 select-none sm:inline" aria-hidden="true">|</span>
        <div class="flex items-center gap-2 sm:flex-wrap sm:gap-x-5 sm:gap-y-1">
            <a href="https://www.youtube.com/c/CMIhospital-com" target="_blank" rel="noopener noreferrer" title="YouTube · CMIhospital.com"
               class="inline-flex size-7 items-center justify-center gap-1.5 rounded-full bg-white/12 text-white/95 transition hover:bg-white/20 hover:text-white sm:size-auto sm:rounded-none sm:bg-transparent sm:hover:bg-transparent sm:hover:underline">
                <x-icon name="youtube" size="size-3.5" />
                <span class="sr-only sm:not-sr-only">CMIhospital.com</span>
            </a>
            <a href="https://www.instagram.com/cmihospital.official/" target="_blank" rel="noopener noreferrer" title="Instagram · cmihospitals"
               class="inline-flex size-7 items-center justify-center gap-1.5 rounded-full bg-white/12 text-white/95 transition hover:bg-white/20 hover:text-white sm:size-auto sm:rounded-none sm:bg-transparent sm:hover:bg-transparent sm:hover:underline">
                <x-icon name="instagram" size="size-3.5" />
                <span class="sr-only sm:not-sr-only">cmihospitals</span>
            </a>
            <a href="https://www.facebook.com/cmihospitals" target="_blank" rel="noopener noreferrer" title="Facebook · CMI Hospital"
               class="inline-flex size-7 items-center justify-center gap-1.5 rounded-full bg-white/12 text-white/95 transition hover:bg-white/20 hover:text-white sm:size-auto sm:rounded-none sm:bg-transparent sm:hover:bg-transparent sm:hover:underline">
                <x-icon name="facebook" size="size-3.5" />
                <span class="sr-only sm:not-sr-only">CMI Hospital</span>
            </a>
        </div>
    </div>
</div>

<header data-navbar class="sticky top-0 z-30 border-b border-brand-200/80 bg-brand-50 transition-shadow data-scrolled:shadow-md data-scrolled:shadow-brand-900/8">
    {{-- Garis pemisah tipis 3px agar tinggi sticky header tetap 67px (selaras dengan sticky toolbar Trix top-[67px]). --}}
    <div class="h-[3px] bg-brand-600/15" aria-hidden="true"></div>

    {{--
        Tiga kolom: [1fr | auto | 1fr]. Kolom kiri & kanan selalu sama lebar, sehingga
        logo di kolom tengah benar-benar berada di tengah layar, apa pun isi kedua sisinya.
        data-scrolled dipasang resources/js/app.js saat halaman di-scroll → muncul bayangan tipis.
    --}}
    <nav class="container-page grid h-16 grid-cols-[1fr_auto_1fr] items-center gap-2 sm:gap-3">
        <div class="flex items-center gap-1 justify-self-start sm:gap-2">
            {{-- Semua menu ada di drawer samping (partials/nav-drawer). --}}
            <button type="button" data-dialog-open="nav-drawer" aria-controls="nav-drawer" aria-expanded="false"
                    class="nav-icon-btn nav-pill-btn -ml-2 sm:ml-0">
                <x-icon name="bars" size="size-5" class="text-brand-800" />
                <span class="hidden text-sm font-semibold sm:inline">{{ __('Menu') }}</span>
                <span class="sr-only sm:hidden">{{ __('Buka menu') }}</span>
            </button>

            {{-- Tombol (bukan kolom); panel pencarian (partials/search-panel, live search) muncul saat ditekan atau dengan "/". --}}
            <button type="button" data-dialog-open="search-panel" aria-controls="search-panel" aria-expanded="false"
                    title="{{ __('Cari artikel') }} (/)" class="nav-icon-btn nav-pill-btn">
                <x-icon name="magnifying-glass" size="size-4" class="text-brand-800" />
                <span class="hidden text-sm font-semibold sm:inline">{{ __('Cari') }}</span>
                <kbd class="hidden rounded border border-brand-200 bg-brand-50/90 px-1.5 py-0.5 font-sans text-[0.75rem] leading-none font-semibold text-brand-700 md:inline-block" aria-hidden="true">/</kbd>
                <span class="sr-only sm:hidden">{{ __('Cari artikel') }}</span>
            </button>
        </div>

        <a href="{{ route('home') }}" class="justify-self-center rounded-lg px-2 py-1 transition hover:bg-white/50 focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:outline-none">
            <x-logo compact subtitle />
        </a>

        <div class="flex items-center gap-1 justify-self-end sm:gap-2">
            {{-- Pilihan bahasa (partials/locale-menu), tersedia di semua halaman. --}}
            <button type="button" popovertarget="locale-menu" title="{{ __('Ganti bahasa') }}"
                    class="nav-icon-btn nav-pill-btn sm:gap-1.5">
                <x-icon name="globe" size="size-4" class="text-brand-800" />
                {{-- Di ponsel cukup ikon (seperti tombol Cari) agar logo di tengah tidak terdesak. --}}
                <span class="hidden text-sm font-semibold sm:inline">{{ config('app.supported_locales')[app()->getLocale()]['short'] }}</span>
                <span class="hidden text-brand-700 md:inline"><x-icon name="chevron-down" size="size-3" /></span>
                <span class="sr-only">{{ __('Ganti bahasa') }}</span>
            </button>

            @guest
                {{-- Hanya tombol Masuk (permintaan user); pendaftaran lewat link "Daftar akun baru" di halaman Masuk. --}}
                {{-- Di ponsel tanpa ikon & tidak terlipat, agar muat bersama tombol bahasa. Ikon dibungkus <span> karena
                     CSS Font Awesome yang tak berlayer menimpa class "hidden". --}}
                <a href="{{ route('login') }}" class="inline-flex h-10 items-center justify-center gap-2 rounded-full bg-brand-600 px-4 text-xs font-semibold whitespace-nowrap text-white shadow-xs transition hover:bg-brand-700 focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2 focus-visible:outline-none sm:px-4.5 sm:text-sm">
                    <span class="hidden sm:inline"><x-icon name="login" size="size-3" /></span> {{ __('Masuk') }}
                </a>
            @else
                {{-- popovertarget: browser sendiri yang membuka/menutup dropdown (partials/notification-menu & user-menu). --}}
                <button type="button" popovertarget="notification-menu" class="nav-icon-btn relative sm:border sm:border-brand-200/90 sm:bg-white/75 sm:shadow-2xs">
                    <x-icon name="bell" size="size-5" class="text-brand-900" />
                    @if ($unreadNotificationCount > 0)
                        <span class="absolute top-1 right-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-600 px-1 text-[0.625rem] font-bold text-white ring-2 ring-brand-50">
                            {{ $unreadNotificationCount > 9 ? '9+' : $unreadNotificationCount }}
                        </span>
                    @endif
                    <span class="sr-only">{{ __('Notifikasi (:count belum dibaca)', ['count' => $unreadNotificationCount]) }}</span>
                </button>

                {{-- Cukup avatar: nama, email & peran sudah tampil di bagian atas dropdown akun. --}}
                <button type="button" popovertarget="user-menu" title="{{ auth()->user()->name }}"
                        class="-mr-1 cursor-pointer rounded-full ring-2 ring-white shadow-xs transition hover:ring-brand-300 focus-visible:ring-brand-500 focus-visible:outline-none">
                    <x-avatar :name="auth()->user()->name" />
                    <span class="sr-only">{{ __('Menu akun :name', ['name' => auth()->user()->name]) }}</span>
                </button>
            @endguest
        </div>
    </nav>
</header>

@include('partials.nav-drawer')
@include('partials.search-panel')
@include('partials.locale-menu')
@auth
    @include('partials.notification-menu')
    @include('partials.user-menu')
@endauth
