{{-- Tombol kontak melayang di kanan bawah: Konsultasi Gratis (WhatsApp) + telepon klinik. Dipasang di layouts/app. --}}
<div class="fixed right-4 bottom-4 z-40 flex flex-col items-end gap-2 sm:right-6 sm:bottom-6 sm:flex-row sm:items-center">
    @if (config('clinic.phone'))
        <a href="tel:{{ config('clinic.phone_international') }}"
           class="btn min-h-11 bg-white text-brand-700 shadow-lg ring-1 ring-brand-100 hover:bg-brand-50 focus-visible:ring-brand-500">
            <x-icon name="phone" size="size-4" />
            <span class="sr-only">{{ __('Telepon klinik') }}:</span> {{ config('clinic.phone') }}
        </a>
    @endif

    <a href="{{ config('clinic.whatsapp_consultation_url') }}" target="_blank" rel="noopener noreferrer"
       class="btn min-h-11 bg-[#0E7A3F] text-white shadow-lg hover:bg-[#0B6534] focus-visible:ring-[#0E7A3F]">
        <x-icon name="whatsapp" size="size-5" /> {{ __('Konsultasi Gratis') }}
    </a>
</div>