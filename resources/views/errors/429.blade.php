@extends('layouts.app')

@section('title', __('Terlalu Banyak Percobaan'))
@section('robots', 'noindex, follow')

@section('content')
    <x-error-page code="429" icon="clock" :title="__('Terlalu banyak percobaan')"
                  :message="__('Demi keamanan akun, permintaan dari perangkat Anda dibatasi sementara. Silakan tunggu sekitar satu menit, lalu coba lagi.')">
        {{-- Halaman sebelumnya bisa berasal dari header Referer (situs luar); hanya alamat situs ini yang dipakai. --}}
        @php($back = parse_url(url()->previous(), PHP_URL_HOST) === request()->getHost() ? url()->previous() : route('home'))
        <a href="{{ $back }}" class="btn btn-primary px-5 py-2.5"><x-icon name="rotate" size="size-4" /> {{ __('Coba lagi') }}</a>
        <a href="{{ config('clinic.whatsapp_consultation_url') }}" target="_blank" rel="noopener noreferrer" class="btn btn-secondary px-5 py-2.5">
            <x-icon name="whatsapp" size="size-4" /> {{ __('Hubungi CS') }}
        </a>
    </x-error-page>
@endsection
