@props(['title' => null])

{{-- Ajakan konsultasi: membuka chat WhatsApp customer service (URL di config/clinic.php). Beranda & detail artikel. --}}
<section {{ $attributes->class('relative overflow-hidden rounded-xl border border-brand-500 bg-brand-600 p-6 text-white shadow-md') }}>
    <div class="h-1 w-12 rounded-full bg-amber-300"></div>
    <div class="mt-4 flex items-center gap-3">
        <span class="flex size-11 shrink-0 items-center justify-center rounded-full bg-white/15 ring-1 ring-white/30">
            <x-icon name="stethoscope" size="size-5" class="text-amber-300" />
        </span>
        <span class="text-xs font-semibold tracking-wider text-white/95 uppercase">{{ __('Layanan Pasien') }}</span>
    </div>
    <h2 class="mt-4 text-lg leading-snug font-bold text-white">{{ $title ?? __('Alami gejala jantung?') }}</h2>
    <p class="mt-1.5 text-sm leading-relaxed text-white/90">{{ __('Konsultasi segera dengan tim :site. Kami siap membantu Anda.', ['site' => config('app.name')]) }}</p>
    <a href="{{ config('clinic.whatsapp_consultation_url') }}" target="_blank" rel="noopener noreferrer"
       class="btn mt-5 min-h-11 w-full bg-whatsapp py-2.5 text-white shadow-sm hover:bg-whatsapp-dark focus-visible:ring-white">
        <x-icon name="whatsapp" size="size-5" /> {{ __('Konsultasi via WhatsApp') }}
    </a>
</section>
