{{--
    Tombol kontak melayang di kanan bawah: Telepon Klinik + Konsultasi WhatsApp. Dipasang di layouts/app.
    Jarak bawah ikut safe-area iPhone (layout memakai viewport-fit=cover) agar tidak tertutup garis home.
    Footer menyisakan ruang di bawah hak cipta (pb-24) supaya teks terakhir tidak tertutup tombol ini.
--}}
<div class="fixed right-4 bottom-[max(1rem,env(safe-area-inset-bottom))] z-40 flex items-center gap-2 sm:right-6 sm:bottom-[max(1.5rem,env(safe-area-inset-bottom))] sm:gap-2.5">
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

    {{-- Tombol Utama Konsultasi WhatsApp, hijau gelap (bg-whatsapp) agar teks putih tetap terbaca.
         Di ponsel bulat ikon saja seperti tombol telepon; teks "Konsultasi Gratis" tetap dibacakan (sr-only). --}}
    <a href="{{ config('clinic.whatsapp_consultation_url') }}" target="_blank" rel="noopener noreferrer"
       title="{{ __('Konsultasi Gratis') }}"
       class="group inline-flex min-h-11 min-w-11 items-center justify-center gap-2.5 rounded-full bg-whatsapp px-3 text-sm font-semibold text-white shadow-lg shadow-emerald-900/15 transition-all duration-200 hover:-translate-y-0.5 hover:bg-whatsapp-dark hover:shadow-xl hover:shadow-emerald-900/20 focus-visible:ring-2 focus-visible:ring-whatsapp focus-visible:ring-offset-2 focus-visible:outline-none active:translate-y-0 sm:px-5">
        {{-- Titik berkedip (hiasan, mulai layar sm): berhenti bila pengguna memilih "kurangi gerakan" di perangkatnya. --}}
        <span class="relative hidden size-2.5 shrink-0 sm:flex" aria-hidden="true">
            <span class="absolute inline-flex size-full rounded-full bg-white opacity-75 motion-safe:animate-ping"></span>
            <span class="relative inline-flex size-2.5 rounded-full bg-white"></span>
        </span>

        <x-icon name="whatsapp" size="size-4" class="transition-transform duration-200 group-hover:scale-110 sm:text-base" />
        <span class="sr-only tracking-wide sm:not-sr-only">{{ __('Konsultasi Gratis') }}</span>
    </a>
</div>
