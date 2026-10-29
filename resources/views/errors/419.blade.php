@extends('layouts.app')

@section('title', 'Halaman Kedaluwarsa')
@section('robots', 'noindex, follow')

@section('content')
    <x-error-page code="419" icon="clock" title="Halaman sudah kedaluwarsa"
                  message="Halaman ini terlalu lama dibuka sehingga permintaan Anda tidak dapat diproses. Muat ulang halaman, lalu coba lagi.">
        <a href="{{ url()->previous() }}" class="btn btn-primary px-5 py-2.5"><x-icon name="rotate" size="size-4" /> Muat ulang</a>
        <a href="{{ route('home') }}" class="btn btn-secondary px-5 py-2.5"><x-icon name="newspaper" size="size-4" /> Ke Beranda</a>
    </x-error-page>
@endsection
