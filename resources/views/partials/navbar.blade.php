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

<header data-navbar class="sticky top-0 z-30 border-b border-gray-200 bg-white transition-shadow data-scrolled:shadow-md data-scrolled:shadow-gray-900/6">
    {{-- Garis ruang 3px agar tinggi sticky header tetap 67px (selaras dengan sticky toolbar Trix top-[67px]). --}}
    <div class="h-[3px] bg-white" aria-hidden="true"></div>

    {{--
        Navbar bergaya Cleveland Clinic:
        - Kiri: tombol burger (di ponsel/tablet) + Logo & nama klinik.
        - Tengah (layar >= lg): tautan navigasi berwarna biru khas CMI + dropdown Topik Jantung / Area Penulis.
        - Kanan: tombol Cari (ikon kaca pembesar hijau khas Cleveland Clinic), pilihan bahasa, dan tombol Masuk persegi biru.
    --}}
    <nav class="container-page flex h-16 items-center justify-between gap-3 sm:gap-5">
        <div class="flex items-center gap-2">
            {{-- Tombol drawer samping di ponsel/tablet (< lg) atau untuk akses cepat menu lengkap. --}}
            <button type="button" data-dialog-open="nav-drawer" aria-controls="nav-drawer" aria-expanded="false"
                    class="flex size-10 cursor-pointer items-center justify-center rounded-lg text-gray-700 transition hover:bg-gray-100 focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:outline-none lg:hidden">
                <x-icon name="bars" size="size-5" />
                <span class="sr-only">{{ __('Buka menu') }}</span>
            </button>

            <a href="{{ route('home') }}" class="rounded-lg py-1 transition hover:opacity-90 focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:outline-none">
                <x-logo compact />
            </a>
        </div>

        {{-- Menu tengah horizontal (layar lebar >= lg) bergaya Cleveland Clinic. --}}
        <div class="hidden items-center gap-6 lg:flex xl:gap-8">
            <a href="{{ route('home') }}"
               @class([
                   'text-sm font-semibold transition hover:text-brand-800',
                   'text-brand-800 underline decoration-2 underline-offset-8' => request()->routeIs('home'),
                   'text-brand-600' => ! request()->routeIs('home'),
               ])>
                {{ __('Beranda') }}
            </a>

            <a href="#hubungi-kami" class="text-sm font-semibold text-brand-600 transition hover:text-brand-800">
                {{ __('Lokasi & Kontak') }}
            </a>

            <a href="{{ config('clinic.whatsapp_consultation_url') }}" target="_blank" rel="noopener noreferrer"
               class="text-sm font-semibold text-brand-600 transition hover:text-brand-800">
                {{ __('Layanan Konsultasi') }}
            </a>

            <button type="button" popovertarget="topics-menu"
                    class="inline-flex cursor-pointer items-center gap-1.5 text-sm font-semibold text-brand-600 transition hover:text-brand-800 focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:outline-none">
                <span>{{ __('Pustaka Jantung') }}</span>
                <x-icon name="chevron-down" size="size-3" />
            </button>

            @auth
                @if (auth()->user()->canWrite())
                    <button type="button" popovertarget="writer-menu"
                            class="inline-flex cursor-pointer items-center gap-1.5 text-sm font-semibold text-brand-600 transition hover:text-brand-800 focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:outline-none">
                        <span>{{ __('Menu Penulis') }}</span>
                        @if (auth()->user()->isAdmin() && $pendingReviewCount > 0)
                            <span class="rounded-full bg-violet-600 px-1.5 py-0.2 text-[0.6875rem] font-bold text-white">{{ $pendingReviewCount }}</span>
                        @endif
                        <x-icon name="chevron-down" size="size-3" />
                    </button>
                @endif
            @endauth
        </div>

        {{-- Sisi kanan: Cari (biru khas CMI), Bahasa, dan tombol CTA Masuk / Akun. --}}
        <div class="flex items-center gap-1.5 sm:gap-3">
            <button type="button" data-dialog-open="search-panel" aria-controls="search-panel" aria-expanded="false"
                    title="{{ __('Cari artikel') }} (/)"
                    class="inline-flex h-10 cursor-pointer items-center justify-center gap-1.5 rounded-lg px-2.5 text-brand-600 transition hover:bg-gray-100 hover:text-brand-800 focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:outline-none">
                <x-icon name="magnifying-glass" size="size-5" />
                <span class="hidden text-sm font-semibold sm:inline">{{ __('Cari') }}</span>
                <span class="sr-only sm:hidden">{{ __('Cari artikel') }}</span>
            </button>

            <button type="button" popovertarget="locale-menu" title="{{ __('Ganti bahasa') }}"
                    class="inline-flex h-10 cursor-pointer items-center justify-center gap-1 rounded-lg px-2.5 text-brand-600 transition hover:bg-gray-100 hover:text-brand-800 focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:outline-none">
                <x-icon name="globe" size="size-4" />
                <span class="hidden text-sm font-semibold sm:inline">{{ config('app.supported_locales')[app()->getLocale()]['short'] }}</span>
                <span class="hidden md:inline"><x-icon name="chevron-down" size="size-3" /></span>
                <span class="sr-only">{{ __('Ganti bahasa') }}</span>
            </button>

            @guest
                <a href="{{ route('login') }}"
                   class="inline-flex h-10 items-center justify-center rounded-md bg-brand-600 px-4 text-xs font-bold whitespace-nowrap text-white shadow-xs transition hover:bg-brand-700 focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2 focus-visible:outline-none sm:px-5 sm:text-sm">
                    {{ __('Masuk') }}
                </a>
            @else
                <button type="button" popovertarget="notification-menu"
                        class="relative flex size-10 cursor-pointer items-center justify-center rounded-lg text-gray-700 transition hover:bg-gray-100 focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:outline-none">
                    <x-icon name="bell" size="size-5" />
                    @if ($unreadNotificationCount > 0)
                        <span class="absolute top-1 right-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-600 px-1 text-[0.625rem] font-bold text-white ring-2 ring-white">
                            {{ $unreadNotificationCount > 9 ? '9+' : $unreadNotificationCount }}
                        </span>
                    @endif
                    <span class="sr-only">{{ __('Notifikasi (:count belum dibaca)', ['count' => $unreadNotificationCount]) }}</span>
                </button>

                <button type="button" popovertarget="user-menu" title="{{ auth()->user()->name }}"
                        class="-mr-1 cursor-pointer rounded-full ring-2 ring-gray-100 transition hover:ring-brand-300 focus-visible:ring-brand-500 focus-visible:outline-none">
                    <x-avatar :name="auth()->user()->name" />
                    <span class="sr-only">{{ __('Menu akun :name', ['name' => auth()->user()->name]) }}</span>
                </button>
            @endguest
        </div>
    </nav>
</header>

{{-- Dropdown "Pustaka Jantung" (Health Library) di navbar desktop. --}}
<div id="topics-menu" popover class="dropdown-panel w-64 overflow-hidden">
    <div class="h-1 bg-gradient-to-r from-brand-600 via-brand-500 to-amber-400" aria-hidden="true"></div>
    <div class="p-2">
        <p class="drawer-heading px-3 pt-1.5">{{ __('Topik Kesehatan Jantung') }}</p>
        @foreach ([__('Hipertensi'), __('Kolesterol'), __('Nyeri Dada'), __('Aritmia'), __('Gaya Hidup')] as $topic)
            <a href="{{ route('home', ['search' => $topic]) }}#daftar-artikel" class="drawer-link">
                <x-icon name="stethoscope" size="size-4" class="text-brand-600" />
                <span>{{ $topic }}</span>
            </a>
        @endforeach
        <div class="my-1.5 border-t border-gray-100"></div>
        <a href="{{ route('home') }}#daftar-artikel" class="drawer-link font-semibold text-brand-700">
            <x-icon name="newspaper" size="size-4" class="text-brand-600" />
            <span>{{ __('Semua Artikel Jantung') }}</span>
        </a>
    </div>
</div>

@auth
    @if (auth()->user()->canWrite())
        <div id="writer-menu" popover class="dropdown-panel w-64 overflow-hidden">
            <div class="h-1 bg-gradient-to-r from-brand-600 via-brand-500 to-amber-400" aria-hidden="true"></div>
            <div class="p-2">
                <p class="drawer-heading px-3 pt-1.5">{{ __('Area Penulis') }}</p>
                <a href="{{ route('dashboard') }}" class="drawer-link">
                    <x-icon name="gauge" size="size-4" class="text-brand-700" /> {{ __('Dashboard') }}
                </a>
                <a href="{{ route('articles.index') }}" class="drawer-link">
                    <x-icon name="document-text" size="size-4" class="text-brand-700" /> {{ __('Artikel Saya') }}
                </a>
                <a href="{{ route('articles.create') }}" class="drawer-link">
                    <x-icon name="plus" size="size-4" class="text-brand-700" /> {{ __('Tulis Artikel') }}
                </a>

                @if (auth()->user()->isAdmin())
                    <div class="my-1.5 border-t border-gray-100"></div>
                    <p class="drawer-heading px-3 pt-1">{{ __('Admin') }}</p>
                    <a href="{{ route('review.index') }}" class="drawer-link">
                        <x-icon name="check-circle" size="size-4" class="text-brand-700" />
                        <span class="flex-1">{{ __('Review') }}</span>
                        @if ($pendingReviewCount > 0)
                            <span class="rounded-full bg-violet-600 px-2 py-0.5 text-xs font-semibold text-white">{{ $pendingReviewCount }}</span>
                        @endif
                    </a>
                    <a href="{{ route('articles.report') }}" class="drawer-link">
                        <x-icon name="chart-bar" size="size-4" class="text-brand-700" /> {{ __('Laporan Artikel') }}
                    </a>
                    <a href="{{ route('users.index') }}" class="drawer-link">
                        <x-icon name="users" size="size-4" class="text-brand-700" /> {{ __('Kelola Pengguna') }}
                    </a>
                @endif
            </div>
        </div>
    @endif
@endauth

@include('partials.nav-drawer')
@include('partials.search-panel')
@include('partials.locale-menu')
@auth
    @include('partials.notification-menu')
    @include('partials.user-menu')
@endauth
