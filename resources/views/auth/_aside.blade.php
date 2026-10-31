{{--
    Isi panel biru kiri halaman Masuk & Daftar (slot "aside" x-auth-card): logo, sambutan singkat,
    ajakan konsultasi WhatsApp, dan catatan keamanan akses. Variabel: $heading, $text.
--}}
<a href="{{ route('home') }}" class="self-start rounded-lg focus-visible:ring-2 focus-visible:ring-white focus-visible:outline-none">
    <x-logo tone="dark" subtitle />
</a>

<div class="my-auto py-10">
    <div class="inline-flex items-center gap-2 rounded-full border border-white/30 bg-white/10 px-3.5 py-1">
        <x-icon name="shield" size="size-3" class="text-amber-300" />
        <span class="eyebrow text-white">{{ __('Layanan Pasien') }}</span>
    </div>
    <div class="mt-5 h-0.5 w-10 rounded-full bg-amber-300" aria-hidden="true"></div>
    <h2 class="mt-4 font-article text-3xl leading-tight font-bold tracking-tight text-white">{{ $heading }}</h2>
    <p class="mt-3 text-sm leading-relaxed text-white/95">{{ $text }}</p>
</div>

<div class="rounded-xl border border-white/25 bg-white/10 p-5">
    <div class="flex items-center gap-2.5">
        <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-white/15 ring-1 ring-white/30">
            <x-icon name="stethoscope" size="size-4" class="text-amber-300" />
        </span>
        <p class="font-semibold text-white">{{ __('Punya keluhan kesehatan?') }}</p>
    </div>
    <p class="mt-2 text-xs leading-relaxed text-white/90">{{ __('Tim kami siap mendampingi konsultasi kesehatan jantung Anda.') }}</p>
    <a href="{{ config('clinic.whatsapp_consultation_url') }}" target="_blank" rel="noopener noreferrer"
       class="mt-3.5 inline-flex items-center gap-2 text-sm font-semibold text-white transition hover:text-amber-200">
        <x-icon name="whatsapp" size="size-4" class="text-[#25D366]" /> {{ __('Konsultasi via WhatsApp') }}
        <x-icon name="chevron-right" size="size-3" />
    </a>
</div>

<p class="mt-8 flex items-center gap-2.5 border-t border-white/20 pt-6 text-sm text-white/95">
    <x-icon name="shield" size="size-4" class="shrink-0 text-amber-300" /> {{ __('Akses aman & terenkripsi ke portal kesehatan Anda.') }}
</p>
