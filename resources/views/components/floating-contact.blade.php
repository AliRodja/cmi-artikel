{{-- Tombol kontak melayang di kanan bawah: Telepon Klinik + Konsultasi WhatsApp. Dipasang di layouts/app. --}}
<div class="fixed right-4 bottom-4 z-40 flex items-center gap-2 sm:right-6 sm:bottom-6 sm:gap-2.5">
    {{-- Tombol Telepon Klinik: di ponsel ikon saja, nama & nomor tetap dibacakan lewat aria-label. --}}
    @if (config('clinic.phone'))
        <a href="tel:{{ config('clinic.phone_international') }}"
           title="{{ __('Telepon klinik') }}: {{ config('clinic.phone') }}"
           aria-label="{{ __('Telepon klinik') }}: {{ config('clinic.phone') }}"
           class="group inline-flex min-h-11 min-w-11 items-center justify-center gap-2 rounded-full border border-slate-200/80 bg-white/90 px-3 text-sm font-semibold text-slate-700 shadow-lg shadow-slate-900/5 backdrop-blur-md transition-all duration-200 hover:-translate-y-0.5 hover:border-slate-300 hover:bg-white hover:text-slate-900 focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2 focus-visible:outline-none sm:px-4">
            <x-icon name="phone" size="size-4" class="text-brand-600 transition-transform duration-200 group-hover:scale-110" />
            <span class="hidden tabular-nums sm:inline">{{ config('clinic.phone') }}</span>
        </a>
    @endif

    {{-- Tombol Utama Konsultasi WhatsApp. Hijau #0E7A3F = hijau WhatsApp yang lolos kontras 4,5:1 (ReadabilityTest). --}}
    <a href="{{ config('clinic.whatsapp_consultation_url') }}" target="_blank" rel="noopener noreferrer"
       class="group inline-flex min-h-11 items-center gap-2.5 rounded-full bg-[#0E7A3F] px-4 text-sm font-semibold text-white shadow-lg shadow-emerald-900/15 transition-all duration-200 hover:-translate-y-0.5 hover:bg-[#0B6534] hover:shadow-xl hover:shadow-emerald-900/20 focus-visible:ring-2 focus-visible:ring-[#0E7A3F] focus-visible:ring-offset-2 focus-visible:outline-none active:translate-y-0 sm:px-5">
        {{-- Titik berkedip (hiasan): berhenti bila pengguna memilih "kurangi gerakan" di perangkatnya. --}}
        <span class="relative flex size-2.5 shrink-0" aria-hidden="true">
            <span class="absolute inline-flex size-full rounded-full bg-emerald-200 opacity-75 motion-safe:animate-ping"></span>
            <span class="relative inline-flex size-2.5 rounded-full bg-emerald-300"></span>
        </span>

        <x-icon name="whatsapp" size="size-4" class="transition-transform duration-200 group-hover:scale-110 sm:text-base" />
        <span class="tracking-wide">{{ __('Konsultasi Gratis') }}</span>
    </a>
</div>
