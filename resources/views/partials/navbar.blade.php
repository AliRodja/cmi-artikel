<header class="sticky top-0 z-30 border-b border-brand-100 bg-brand-50">
    <nav class="container-page flex flex-wrap items-center justify-between gap-3 py-3">
        <a href="{{ route('home') }}">
            <x-logo />
        </a>

        <div class="order-last flex w-full flex-wrap items-center gap-1 md:order-none md:w-auto">
            <a href="{{ route('home') }}"
               @class(['nav-link', 'nav-link-active' => request()->routeIs('home', 'public.*')])>Berita &amp; Artikel</a>

            @auth
                @php
                    // Admin yang membuka artikel penulis lain sedang "me-review", bukan di Artikel Saya.
                    $routeArticle = request()->route('article');
                    $viewingOthers = request()->routeIs('articles.*')
                        && $routeArticle instanceof \App\Models\Article
                        && $routeArticle->user_id !== auth()->id();
                @endphp
                <a href="{{ route('dashboard') }}"
                   @class(['nav-link', 'nav-link-active' => request()->routeIs('dashboard')])>Dashboard</a>
                <a href="{{ route('articles.index') }}"
                   @class(['nav-link', 'nav-link-active' => request()->routeIs('articles.*') && ! request()->routeIs('articles.report') && ! $viewingOthers])>Artikel Saya</a>

                @if (auth()->user()->isAdmin())
                    <a href="{{ route('review.index') }}"
                       @class(['nav-link inline-flex items-center gap-1.5', 'nav-link-active' => request()->routeIs('review.*') || $viewingOthers])>
                        Review
                        @if ($pendingReviewCount > 0)
                            <span class="rounded-full bg-violet-600 px-1.5 text-xs font-semibold text-white">{{ $pendingReviewCount }}</span>
                        @endif
                    </a>
                    <a href="{{ route('articles.report') }}"
                       @class(['nav-link', 'nav-link-active' => request()->routeIs('articles.report')])>Laporan Artikel</a>
                @endif
            @endauth
        </div>

        <div class="flex items-center gap-3">
            @guest
                <a href="{{ route('login') }}" class="btn btn-secondary btn-sm">Masuk</a>
                <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Daftar</a>
            @else
                <div class="hidden items-center gap-2 sm:flex">
                    <x-avatar :name="auth()->user()->name" />
                    <div class="text-right leading-tight">
                        <p class="text-sm font-semibold text-gray-900">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-gray-500">{{ auth()->user()->role_label }}</p>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-secondary btn-sm">Keluar</button>
                </form>
            @endguest
        </div>
    </nav>
</header>
