@props(['title' => 'Punya keluhan kesehatan?'])

{{-- Ajakan konsultasi: membuka chat WhatsApp customer service (URL di config/clinic.php). Beranda & detail artikel. --}}
<section {{ $attributes->class('overflow-hidden rounded-xl bg-gradient-to-br from-brand-600 to-brand-900 p-5 text-white shadow-md') }}>
    <span class="flex size-11 items-center justify-center rounded-full bg-white/15">
        <x-icon name="stethoscope" size="size-5" />
    </span>
    <h2 class="mt-4 text-lg leading-snug font-bold">{{ $title }}</h2>
    <p class="mt-1 text-sm text-brand-100">Konsultasi segera dengan tim {{ config('app.name') }}. Kami siap membantu Anda.</p>
    <a href="{{ config('clinic.whatsapp_consultation_url') }}" target="_blank" rel="noopener noreferrer"
       class="btn mt-5 w-full bg-[#25D366] text-white shadow-sm hover:bg-[#1DA851] focus-visible:ring-white">
        <x-icon name="whatsapp" size="size-5" /> Konsultasi via WhatsApp
    </a>
</section>
