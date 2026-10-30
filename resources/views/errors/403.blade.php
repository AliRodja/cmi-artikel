@extends('layouts.app')

@section('title', __('Akses Ditolak'))
@section('robots', 'noindex, follow')

@section('content')
    <x-error-page code="403" icon="lock" :title="__('Akses ditolak')"
                  :message="__('Anda tidak memiliki izin untuk membuka halaman ini atau melakukan tindakan tersebut. Bila menurut Anda ini keliru, hubungi admin.')">
        @if (auth()->user()?->canWrite())
            <a href="{{ route('articles.index') }}" class="btn btn-primary px-5 py-2.5"><x-icon name="document-text" size="size-4" /> {{ __('Ke Artikel Saya') }}</a>
        @endif
        <a href="{{ route('home') }}" @class(['btn px-5 py-2.5', 'btn-secondary' => auth()->user()?->canWrite(), 'btn-primary' => ! auth()->user()?->canWrite()])>
            <x-icon name="newspaper" size="size-4" /> {{ __('Ke Beranda') }}
        </a>
    </x-error-page>
@endsection
