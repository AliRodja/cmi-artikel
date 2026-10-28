{{--
    Panel pencarian dari ikon kaca pembesar di navbar. Mengirim GET ke beranda
    (?search=...), memakai pencarian artikel Published yang sudah ada di
    PublicArticleController, jadi tidak perlu route baru.
--}}
<dialog id="search-panel" class="search-panel" aria-label="Cari artikel">
    <div class="container-page py-5">
        <form action="{{ route('home') }}" method="GET" role="search" class="flex items-center gap-2">
            <div class="relative flex-1">
                <x-icon name="magnifying-glass" size="size-5" class="pointer-events-none absolute top-1/2 left-4 -translate-y-1/2 text-gray-400" />
                <label for="navbar-search" class="sr-only">Cari artikel</label>
                {{-- autofocus: showModal() langsung memindahkan kursor ke kolom ini. --}}
                <input type="search" id="navbar-search" name="search" autofocus
                       value="{{ request()->routeIs('home') ? request('search') : '' }}"
                       placeholder="Cari judul atau isi artikel..."
                       class="form-input py-3 pl-12 text-base">
            </div>
            <button type="submit" class="btn btn-primary py-3">Cari</button>
            <button type="button" data-dialog-close
                    class="flex size-11 shrink-0 cursor-pointer items-center justify-center rounded-lg text-gray-500 transition hover:bg-gray-100 hover:text-gray-900">
                <x-icon name="x-mark" size="size-5" />
                <span class="sr-only">Tutup pencarian</span>
            </button>
        </form>
        <p class="mt-2 hidden text-xs text-gray-500 sm:block">Tekan Enter untuk mencari · Esc untuk menutup</p>
    </div>
</dialog>
