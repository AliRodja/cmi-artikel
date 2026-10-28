{{--
    Panel pencarian dari ikon kaca pembesar di navbar.
    - Live search: saat mengetik, resources/js/live-search.js mengambil hasil dari route articles.live-search
      (PublicArticleController@liveSearch) dan menampilkannya di [data-live-search-results].
    - Enter tanpa memilih hasil = form GET biasa ke beranda (?search=...), halaman hasil lengkap.
--}}
<dialog id="search-panel" class="search-panel" aria-label="Cari artikel">
    <div class="container-page py-5">
        <div class="mx-auto max-w-3xl">
            <form action="{{ route('home') }}" method="GET" role="search" class="flex items-center gap-2"
                  data-live-search-url="{{ route('articles.live-search') }}">
                <div class="relative flex-1">
                    <x-icon name="magnifying-glass" size="size-5" class="pointer-events-none absolute top-1/2 left-4 -translate-y-1/2 text-gray-400" />
                    <label for="navbar-search" class="sr-only">Cari artikel</label>
                    {{-- autofocus: showModal() langsung memindahkan kursor ke kolom ini. --}}
                    <input type="search" id="navbar-search" name="search" autofocus autocomplete="off"
                           role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="live-search-list"
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

            {{-- Sebelum mengetik: pintasan jenis artikel. --}}
            <div data-live-search-idle class="mt-5">
                <p class="text-xs font-semibold tracking-wider text-gray-400 uppercase">Telusuri jenis artikel</p>
                <div class="mt-2 flex flex-wrap gap-2">
                    @foreach (\App\Models\Article::CATEGORIES as $value => $label)
                        <a href="{{ route('home', ['jenis' => $value]) }}#daftar-artikel"
                           class="rounded-full border border-gray-200 px-4 py-1.5 text-sm font-medium text-gray-700 transition hover:border-brand-300 hover:text-brand-700">{{ $label }}</a>
                    @endforeach
                </div>
            </div>

            <div data-live-search-results aria-live="polite" class="mt-4 max-h-[60vh] overflow-y-auto transition-opacity aria-busy:opacity-60" hidden></div>

            <p class="mt-4 hidden text-xs text-gray-500 sm:block">
                Ketik minimal 2 huruf · <kbd class="font-sans">↑</kbd> <kbd class="font-sans">↓</kbd> pilih hasil · Enter buka · Esc tutup
            </p>
        </div>
    </div>
</dialog>
