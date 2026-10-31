{{--
    Isi panel biru kiri halaman Masuk & Daftar (slot "aside" x-auth-card): logo, sambutan singkat,
    ajakan konsultasi WhatsApp, dan catatan keamanan akses. Variabel: $heading, $text.
--}}
<a href="{{ route('home') }}" class="self-start rounded-lg focus-visible:ring-2 focus-visible:ring-white focus-visible:outline-none">
    <x-logo tone="dark" />
</a>

<div class="my-auto py-10">
    <h2 class="text-3xl leading-tight font-extrabold tracking-tight">{{ $heading }}</h2>
    <p class="mt-3 text-sm leading-relaxed text-brand-100">{{ $text }}</p>
</div>

<div class="rounded-xl bg-white/10 p-5">
    <p class="font-semibold">{{ __('Punya keluhan kesehatan?') }}</p>
    <a href="{{ config('clinic.whatsapp_consultation_url') }}" target="_blank" rel="noopener noreferrer"
       class="mt-3 inline-flex items-center gap-2 text-sm font-medium text-brand-100 transition hover:text-white">
        <x-icon name="whatsapp" size="size-4" class="text-[#25D366]" /> {{ __('Konsultasi via WhatsApp') }}
        <x-icon name="chevron-right" size="size-3" />
    </a>
</div>

<p class="mt-8 flex items-center gap-2 border-t border-brand-900 pt-6 text-sm text-brand-100">
    <x-icon name="shield" size="size-4" class="shrink-0" /> {{ __('Akses aman & terenkripsi ke portal kesehatan Anda.') }}
</p>
