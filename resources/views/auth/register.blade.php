@extends('layouts.app')

@section('title', __('Daftar'))

@section('content')
    <x-auth-card :title="__('Buat akun baru')" :subtitle="__('Gratis. Cukup isi nama, email, dan password.')">
        <x-slot:aside>
            @include('auth._aside', [
                'heading' => __('Bergabung bersama kami'),
                'text' => __('Buat akun untuk menyimpan artikel, ikut berdiskusi, dan mendapat kabar kesehatan terbaru dari :site.', ['site' => config('app.name')]),
            ])
        </x-slot:aside>

        <form action="{{ route('register') }}" method="POST">
            @csrf

            <x-input name="name" :label="__('Nama lengkap')" icon="user" :placeholder="__('Nama Anda')"
                     required autofocus autocomplete="name" maxlength="255" />
            <x-input name="email" :label="__('Email')" type="email" icon="envelope" :placeholder="__('nama@email.com')"
                     required autocomplete="email" maxlength="255" />
            <div class="grid gap-x-4 sm:grid-cols-2">
                <x-input name="password" :label="__('Password')" type="password" icon="lock" :placeholder="__('Buat password')"
                         :hint="__('Minimal 8 karakter.')" required minlength="8" autocomplete="new-password" />
                <x-input name="password_confirmation" :label="__('Konfirmasi password')" type="password" icon="lock" :placeholder="__('Ulangi password')"
                         required autocomplete="new-password" />
            </div>
            @include('auth._password-match')

            <button type="submit" class="btn btn-primary mt-2 w-full py-2.5">
                <x-icon name="user-plus" size="size-4" /> {{ __('Daftar') }}
            </button>
        </form>

        <div class="my-6 flex items-center gap-3 text-xs text-gray-500">
            <span class="h-px flex-1 bg-gray-200"></span> {{ __('Sudah punya akun?') }} <span class="h-px flex-1 bg-gray-200"></span>
        </div>
        <a href="{{ route('login') }}" class="btn btn-secondary w-full py-2.5">
            <x-icon name="login" size="size-4" /> {{ __('Masuk') }}
        </a>

        <x-slot:footer>
            <a href="{{ route('home') }}" class="link inline-flex items-center gap-1.5">
                <x-icon name="arrow-left" size="size-3" /> {{ __('Kembali ke beranda') }}
            </a>
        </x-slot:footer>
    </x-auth-card>
@endsection
