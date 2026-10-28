@php
    // Info klinik dari config/clinic.php (.env). Baris yang belum diisi tidak ditampilkan.
    $clinic = config('clinic');
@endphp

<footer class="bg-brand-600 text-white">
    <div class="container-page grid gap-10 py-12 sm:grid-cols-2 lg:grid-cols-[1.2fr_1.3fr_0.8fr]">
        <div>
            <x-logo tone="dark" />
            <p class="mt-4 max-w-sm text-sm leading-relaxed text-white/90">
                Informasi kesehatan, kabar terbaru, dan pengumuman resmi untuk pasien dan masyarakat.
            </p>
            @if ($clinic['accreditation'])
                <p class="mt-4 inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1.5 text-sm">
                    <x-icon name="shield" size="size-4" /> {{ $clinic['accreditation'] }}
                </p>
            @endif
        </div>

        <div>
            <p class="eyebrow text-white/90">Hubungi Kami</p>
            <ul class="mt-4 space-y-3 text-sm text-white">
                @if ($clinic['address'])
                    <li class="flex gap-3">
                        <x-icon name="map-pin" size="size-4" class="mt-0.5 text-white/90" />
                        <span>
                            {{ $clinic['address'] }}
                            @if ($clinic['maps_url'])
                                <a href="{{ $clinic['maps_url'] }}" target="_blank" rel="noopener noreferrer" class="mt-1 block font-semibold underline underline-offset-2 hover:text-white/80">Lihat di peta</a>
                            @endif
                        </span>
                    </li>
                @endif
                @if ($clinic['hours'])
                    <li class="flex gap-3">
                        <x-icon name="clock" size="size-4" class="mt-0.5 text-white/90" />
                        <span>{{ $clinic['hours'] }}</span>
                    </li>
                @endif
                <li class="flex gap-3">
                    <x-icon name="whatsapp" size="size-4" class="mt-0.5 text-white/90" />
                    <a href="{{ $clinic['whatsapp_consultation_url'] }}" target="_blank" rel="noopener noreferrer" class="hover:underline">
                        WhatsApp CS: <span class="font-semibold">{{ $clinic['whatsapp_display'] }}</span>
                    </a>
                </li>
                @if ($clinic['email'])
                    <li class="flex gap-3">
                        <x-icon name="envelope" size="size-4" class="mt-0.5 text-white/90" />
                        <a href="mailto:{{ $clinic['email'] }}" class="break-all hover:underline">{{ $clinic['email'] }}</a>
                    </li>
                @endif
            </ul>
        </div>

        <div class="lg:justify-self-end">
            <p class="eyebrow text-white/90">Navigasi</p>
            <ul class="mt-4 space-y-3 text-sm text-white">
                <li><a href="{{ route('home') }}" class="hover:underline">Berita &amp; Artikel</a></li>
                @auth
                    @if (auth()->user()->canWrite())
                        <li><a href="{{ route('dashboard') }}" class="hover:underline">Dashboard</a></li>
                    @endif
                    <li><a href="{{ route('bookmarks.index') }}" class="hover:underline">Baca Nanti</a></li>
                @else
                    <li><a href="{{ route('login') }}" class="hover:underline">Masuk</a></li>
                    <li><a href="{{ route('register') }}" class="hover:underline">Daftar</a></li>
                @endauth
            </ul>
        </div>
    </div>

    <div class="border-t border-white/20">
        <p class="container-page py-5 text-sm text-white/90">
            &copy; {{ date('Y') }} {{ config('app.name') }}. Seluruh hak cipta dilindungi.
        </p>
    </div>
</footer>
