{{--
    Menu navigasi samping (hanya navigasi). Dibuka oleh tombol burger di navbar (resources/js/app.js).
    Akun (profil, Kelola Akun, Baca Nanti, Keluar) ada di dropdown kanan: partials/user-menu.
    Kelola Pengguna (semua user) khusus admin, ada di bagian Admin di bawah.
    Data $pendingReviewCount datang dari View Composer navbar (AppServiceProvider),
    dan ikut tersedia di sini karena @include mewarisi data view induknya.
--}}
<dialog id="nav-drawer" class="nav-drawer" aria-label="Menu navigasi">
    <div class="flex h-full flex-col">
        <div class="flex items-center justify-between border-b border-brand-100 bg-brand-50 px-5 py-3">
            <x-logo />
            <button type="button" data-dialog-close
                    class="flex size-9 cursor-pointer items-center justify-center rounded-lg text-gray-500 transition hover:bg-white hover:text-gray-900">
                <x-icon name="x-mark" size="size-5" />
                <span class="sr-only">Tutup menu</span>
            </button>
        </div>

        <nav class="flex-1 space-y-6 overflow-y-auto px-3 py-5">
            <div>
                <p class="drawer-heading">Situs</p>
                <a href="{{ route('home') }}" @class(['drawer-link', 'drawer-link-active' => request()->routeIs('home', 'public.*')])>
                    <x-icon name="newspaper" class="text-gray-400" /> Berita &amp; Artikel
                </a>
            </div>

            @auth
                @php
                    // Admin yang membuka artikel penulis lain sedang "me-review", bukan di Artikel Saya.
                    $routeArticle = request()->route('article');
                    $viewingOthers = request()->routeIs('articles.*')
                        && $routeArticle instanceof \App\Models\Article
                        && $routeArticle->user_id !== auth()->id();
                @endphp

                @if (auth()->user()->canWrite())
                <div>
                    <p class="drawer-heading">Area Penulis</p>
                    <a href="{{ route('dashboard') }}" @class(['drawer-link', 'drawer-link-active' => request()->routeIs('dashboard')])>
                        <x-icon name="gauge" class="text-gray-400" /> Dashboard
                    </a>
                    <a href="{{ route('articles.index') }}"
                       @class(['drawer-link', 'drawer-link-active' => request()->routeIs('articles.*') && ! request()->routeIs('articles.report', 'articles.create') && ! $viewingOthers])>
                        <x-icon name="document-text" class="text-gray-400" /> Artikel Saya
                    </a>
                    <a href="{{ route('articles.create') }}" @class(['drawer-link', 'drawer-link-active' => request()->routeIs('articles.create')])>
                        <x-icon name="plus" class="text-gray-400" /> Tulis Artikel
                    </a>
                </div>
                @endif

                @if (auth()->user()->isAdmin())
                    <div>
                        <p class="drawer-heading">Admin</p>
                        <a href="{{ route('review.index') }}" @class(['drawer-link', 'drawer-link-active' => request()->routeIs('review.*') || $viewingOthers])>
                            <x-icon name="check-circle" class="text-gray-400" />
                            <span class="flex-1">Review</span>
                            @if ($pendingReviewCount > 0)
                                <span class="rounded-full bg-violet-600 px-2 text-xs font-semibold text-white">{{ $pendingReviewCount }}</span>
                            @endif
                        </a>
                        <a href="{{ route('articles.report') }}" @class(['drawer-link', 'drawer-link-active' => request()->routeIs('articles.report')])>
                            <x-icon name="chart-bar" class="text-gray-400" /> Laporan Artikel
                        </a>
                        <a href="{{ route('users.index') }}" @class(['drawer-link', 'drawer-link-active' => request()->routeIs('users.*')])>
                            <x-icon name="users" class="text-gray-400" /> Kelola Pengguna
                        </a>
                    </div>
                @endif
            @endauth
        </nav>
    </div>
</dialog>
