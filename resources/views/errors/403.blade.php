@extends('layouts.app')

@section('title', 'Akses Ditolak')

@section('content')
    <div class="mx-auto max-w-md py-12 text-center">
        <p class="text-6xl font-bold text-brand-700">403</p>
        <h1 class="mt-4 text-2xl font-bold text-gray-900">Akses Ditolak</h1>
        <p class="mt-2 text-sm text-gray-500">Anda tidak memiliki izin untuk membuka halaman ini. Anda hanya dapat mengelola artikel milik Anda sendiri.</p>

        <div class="mt-6 flex justify-center gap-2">
            @auth
                <a href="{{ route('articles.index') }}" class="btn btn-primary">Ke Artikel Saya</a>
            @endauth
            <a href="{{ route('home') }}" class="btn btn-secondary">Ke Beranda</a>
        </div>
    </div>
@endsection
