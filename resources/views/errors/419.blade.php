@extends('layouts.app')

@section('title', __('Halaman Kedaluwarsa'))
@section('robots', 'noindex, follow')

@section('content')
    <x-error-page code="419" icon="clock" :title="__('Halaman sudah kedaluwarsa')"
                  :message="__('Halaman ini terlalu lama dibuka sehingga permintaan Anda tidak dapat diproses. Muat ulang halaman, lalu coba lagi.')">
        {{-- Halaman sebelumnya bisa berasal dari header Referer (situs luar); hanya alamat situs ini yang dipakai. --}}
        @php($back = parse_url(url()->previous(), PHP_URL_HOST) === request()->getHost() ? url()->previous() : route('home'))
        <a href="{{ $back }}" class="btn btn-primary px-5 py-2.5"><x-icon name="rotate" size="size-4" /> {{ __('Muat ulang') }}</a>
        <a href="{{ route('home') }}" class="btn btn-secondary px-5 py-2.5"><x-icon name="newspaper" size="size-4" /> {{ __('Ke Beranda') }}</a>
    </x-error-page>
@endsection
