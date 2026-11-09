@php
    // Info klinik dari config/clinic.php (.env). Baris yang belum diisi tidak ditampilkan.
    $clinic = config('clinic');
@endphp

<footer class="bg-brand-600 text-white">
    <div class="container-page grid gap-10 py-12 sm:grid-cols-2 lg:grid-cols-[1.2fr_1.3fr_0.8fr]">
        <div>
            <x-logo tone="dark" />
            <p class="mt-4 max-w-sm text-sm leading-relaxed text-white/90">
                {{ __('Artikel kesehatan jantung untuk pasien dan masyarakat: gejala, pencegahan, dan perawatan.') }}
            </p>
            @if ($clinic['accreditation'])
                <p class="mt-4 inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1.5 text-sm">
                    <x-icon name="shield" size="size-4" /> {{ $clinic['accreditation'] }}
                </p>
            @endif

            {{-- Media sosial resmi (config/clinic.php). Tombol 44px agar mudah diketuk. --}}
            <p class="eyebrow mt-6 text-white/90">{{ __('Ikuti Kami') }}</p>
            <ul class="mt-3 flex flex-wrap gap-2">
                @foreach ($clinic['social'] as $social)
                    <li>
                        <a href="{{ $social['url'] }}" target="_blank" rel="noopener noreferrer" title="{{ $social['label'] }}"
                           class="flex size-11 items-center justify-center rounded-full bg-white/10 text-white ring-1 ring-white/20 transition hover:bg-white hover:text-brand-700 focus-visible:ring-2 focus-visible:ring-white focus-visible:outline-none">
                            <x-icon :name="$social['icon']" size="size-5" />
                            <span class="sr-only">{{ __(':site di :network', ['site' => config('app.name'), 'network' => $social['label']]) }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        <div id="hubungi-kami" class="scroll-mt-24">
            <p class="eyebrow text-white/90">{{ __('Hubungi Kami') }}</p>
            <ul class="mt-4 space-y-2 text-sm text-white">
                @if ($clinic['address'])
                    <li class="flex gap-3">
                        <x-icon name="map-pin" size="size-4" class="mt-0.5 text-white/90" />
                        <span>
                            {{ $clinic['address'] }}
                            @if ($clinic['maps_url'])
                                <a href="{{ $clinic['maps_url'] }}" target="_blank" rel="noopener noreferrer" class="inline-flex min-h-11 items-center font-semibold underline underline-offset-2 hover:text-white/80">{{ __('Lihat di peta') }}</a>
                            @endif
                        </span>
                    </li>
                @endif
                @if ($clinic['hours'])
                    <li class="flex gap-3">
                        <x-icon name="clock" size="size-4" class="mt-0.5 text-white/90" />
                        {{-- Jam dari .env; diterjemahkan bila ada di lang/en.json ("Buka setiap hari, 24 jam"). --}}
                        <span>{{ __($clinic['hours']) }}</span>
                    </li>
                @endif
                @if ($clinic['phone'])
                    <li class="flex items-center gap-3">
                        <x-icon name="phone" size="size-4" class="text-white/90" />
                        <a href="tel:{{ $clinic['phone_international'] }}" class="inline-flex min-h-11 items-center gap-1 hover:underline">
                            {{ __('Telepon') }}: <span class="font-semibold">{{ $clinic['phone'] }}</span>
                        </a>
                    </li>
                @endif
                @if ($clinic['whatsapp'])
                    <li class="flex items-center gap-3">
                        <x-icon name="whatsapp" size="size-4" class="text-white/90" />
                        <a href="{{ $clinic['whatsapp_url'] }}" target="_blank" rel="noopener noreferrer" class="inline-flex min-h-11 items-center gap-1 hover:underline">
                            WhatsApp: <span class="font-semibold">{{ $clinic['whatsapp'] }}</span>
                        </a>
                    </li>
                @endif
                @if ($clinic['email'])
                    <li class="flex items-center gap-3">
                        <x-icon name="envelope" size="size-4" class="text-white/90" />
                        <a href="mailto:{{ $clinic['email'] }}" class="inline-flex min-h-11 items-center break-all hover:underline">{{ $clinic['email'] }}</a>
                    </li>
                @endif
            </ul>
        </div>

        <div class="lg:justify-self-end">
            <p class="eyebrow text-white/90">{{ __('Navigasi') }}</p>
            <ul class="mt-2 text-sm text-white">
                <li><a href="{{ route('home') }}" class="inline-flex min-h-11 items-center hover:underline">{{ __('Artikel Jantung') }}</a></li>
                @auth
                    @if (auth()->user()->canWrite())
                        <li><a href="{{ route('dashboard') }}" class="inline-flex min-h-11 items-center hover:underline">{{ __('Dashboard') }}</a></li>
                    @endif
                    <li><a href="{{ route('bookmarks.index') }}" class="inline-flex min-h-11 items-center hover:underline">{{ __('Baca Nanti') }}</a></li>
                @else
                    <li><a href="{{ route('login') }}" class="inline-flex min-h-11 items-center hover:underline">{{ __('Masuk') }}</a></li>
                    <li><a href="{{ route('register') }}" class="inline-flex min-h-11 items-center hover:underline">{{ __('Daftar') }}</a></li>
                @endauth
            </ul>
        </div>
    </div>

    <div class="border-t border-white/20">
        <p class="container-page pt-5 pb-[calc(6rem+env(safe-area-inset-bottom))] text-sm text-white/90">
            &copy; {{ date('Y') }} {{ config('app.name') }}. {{ __('Seluruh hak cipta dilindungi.') }}
        </p>
    </div>
</footer>
