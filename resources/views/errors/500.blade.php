@extends('layouts.minimal')

@section('title', 'Terjadi Kesalahan')

@section('content')
    <x-error-page code="500" icon="exclamation-triangle" title="Terjadi kesalahan"
                  message="Maaf, server kami sedang mengalami gangguan. Tim kami sudah diberi tahu. Silakan coba beberapa saat lagi.">
        <a href="{{ url('/') }}" class="btn btn-primary px-5 py-2.5"><x-icon name="rotate" size="size-4" /> Coba lagi</a>
        <a href="{{ config('clinic.whatsapp_consultation_url') }}" target="_blank" rel="noopener noreferrer" class="btn btn-secondary px-5 py-2.5">
            <x-icon name="whatsapp" size="size-4" /> Hubungi CS via WhatsApp
        </a>
    </x-error-page>
@endsection
