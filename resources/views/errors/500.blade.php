@extends('layouts.minimal')

@section('title', __('Terjadi Kesalahan'))

@section('content')
    <x-error-page code="500" icon="exclamation-triangle" :title="__('Terjadi kesalahan')"
                  :message="__('Maaf, server kami sedang mengalami gangguan. Tim kami sudah diberi tahu. Silakan coba beberapa saat lagi.')">
        <a href="{{ url('/') }}" class="btn btn-primary px-5 py-2.5"><x-icon name="rotate" size="size-4" /> {{ __('Coba lagi') }}</a>
        <a href="{{ config('clinic.whatsapp_consultation_url') }}" target="_blank" rel="noopener noreferrer" class="btn btn-secondary px-5 py-2.5">
            <x-icon name="whatsapp" size="size-4" /> {{ __('Hubungi CS via WhatsApp') }}
        </a>
    </x-error-page>
@endsection
