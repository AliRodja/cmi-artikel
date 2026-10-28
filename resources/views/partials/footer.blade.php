<footer class="bg-brand-600 text-white">
    <div class="container-page grid gap-8 py-12 sm:grid-cols-2">
        <div>
            <x-logo tone="dark" />
            <p class="mt-4 max-w-sm text-sm leading-relaxed text-white/85">
                Informasi kesehatan, kabar terbaru, dan pengumuman resmi untuk pasien dan masyarakat.
            </p>
        </div>

        <div class="sm:justify-self-end">
            <p class="eyebrow text-white/70">Navigasi</p>
            <ul class="mt-4 space-y-2 text-sm text-white/90">
                <li><a href="{{ route('home') }}" class="hover:text-white hover:underline">Berita &amp; Artikel</a></li>
                @auth
                    @if (auth()->user()->canWrite())
                        <li><a href="{{ route('dashboard') }}" class="hover:text-white hover:underline">Dashboard</a></li>
                    @endif
                    <li><a href="{{ route('bookmarks.index') }}" class="hover:text-white hover:underline">Baca Nanti</a></li>
                @else
                    <li><a href="{{ route('login') }}" class="hover:text-white hover:underline">Masuk</a></li>
                @endauth
            </ul>
        </div>
    </div>

    <div class="border-t border-white/15">
        <p class="container-page py-5 text-xs text-white/75">
            &copy; {{ date('Y') }} {{ config('app.name') }}. Seluruh hak cipta dilindungi.
        </p>
    </div>
</footer>
