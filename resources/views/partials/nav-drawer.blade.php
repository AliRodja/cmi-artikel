{{--
    Menu navigasi samping (hanya navigasi). Dibuka oleh tombol burger di navbar (resources/js/app.js).
    Akun (profil, Kelola Akun, Baca Nanti, Keluar) ada di dropdown kanan: partials/user-menu.
    Kelola Pengguna (semua user) khusus admin, ada di bagian Admin di bawah.
    Data $pendingReviewCount datang dari View Composer navbar (AppServiceProvider),
    dan ikut tersedia di sini karena @include mewarisi data view induknya.
--}}
<dialog id="nav-drawer" class="nav-drawer" aria-label="{{ __('Menu navigasi') }}">
    <div class="flex h-full flex-col">
        <div class="h-1 bg-gradient-to-r from-brand-950 via-brand-600 to-amber-400" aria-hidden="true"></div>

        <div class="flex items-center justify-between border-b border-brand-100 bg-brand-50/80 px-5 py-3.5">
            <x-logo />
            <button type="button" data-dialog-close
                    class="flex size-9 cursor-pointer items-center justify-center rounded-full border border-brand-200/80 bg-white text-gray-600 shadow-2xs transition hover:border-brand-300 hover:text-gray-900">
                <x-icon name="x-mark" size="size-4" />
                <span class="sr-only">{{ __('Tutup menu') }}</span>
            </button>
        </div>

        <nav class="flex-1 space-y-6 overflow-y-auto px-3.5 py-5">
            <div>
                <p class="drawer-heading">{{ __('Situs') }}</p>
                <a href="{{ route('home') }}" @class(['drawer-link', 'drawer-link-active' => request()->routeIs('home', 'public.*')])>
                    <x-icon name="newspaper" class="text-brand-700" /> {{ __('Artikel Jantung') }}
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
                    <p class="drawer-heading">{{ __('Area Penulis') }}</p>
                    <div class="space-y-1">
                        <a href="{{ route('dashboard') }}" @class(['drawer-link', 'drawer-link-active' => request()->routeIs('dashboard')])>
                            <x-icon name="gauge" class="text-brand-700" /> {{ __('Dashboard') }}
                        </a>
                        <a href="{{ route('articles.index') }}"
                           @class(['drawer-link', 'drawer-link-active' => request()->routeIs('articles.*') && ! request()->routeIs('articles.report', 'articles.create') && ! $viewingOthers])>
                            <x-icon name="document-text" class="text-brand-700" /> {{ __('Artikel Saya') }}
                        </a>
                        <a href="{{ route('articles.create') }}" @class(['drawer-link', 'drawer-link-active' => request()->routeIs('articles.create')])>
                            <x-icon name="plus" class="text-brand-700" /> {{ __('Tulis Artikel') }}
                        </a>
                    </div>
                </div>
                @endif

                @if (auth()->user()->isAdmin())
                    <div>
                        <p class="drawer-heading">{{ __('Admin') }}</p>
                        <div class="space-y-1">
                            <a href="{{ route('review.index') }}" @class(['drawer-link', 'drawer-link-active' => request()->routeIs('review.*') || $viewingOthers])>
                                <x-icon name="check-circle" class="text-brand-700" />
                                <span class="flex-1">{{ __('Review') }}</span>
                                @if ($pendingReviewCount > 0)
                                    <span class="rounded-full bg-violet-600 px-2 py-0.5 text-xs font-semibold text-white">{{ $pendingReviewCount }}</span>
                                @endif
                            </a>
                            <a href="{{ route('articles.report') }}" @class(['drawer-link', 'drawer-link-active' => request()->routeIs('articles.report')])>
                                <x-icon name="chart-bar" class="text-brand-700" /> {{ __('Laporan Artikel') }}
                            </a>
                            <a href="{{ route('users.index') }}" @class(['drawer-link', 'drawer-link-active' => request()->routeIs('users.*')])>
                                <x-icon name="users" class="text-brand-700" /> {{ __('Kelola Pengguna') }}
                            </a>
                        </div>
                    </div>
                @endif
            @endauth
        </nav>

        <div class="border-t border-gray-100 bg-gray-50/80 px-5 py-4">
            <p class="text-xs font-semibold tracking-wider text-brand-800 uppercase">{{ __('Pusat Informasi Jantung') }}</p>
            <p class="mt-0.5 text-xs leading-relaxed text-gray-600">{{ config('app.name') }}</p>
        </div>
    </div>
</dialog>
