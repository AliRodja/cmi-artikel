@extends('layouts.app')

@section('title', __('Masuk'))

@section('content')
    <x-auth-card :title="__('Masuk ke akun Anda')" :subtitle="__('Gunakan email dan password yang Anda daftarkan.')">
        <x-slot:aside>
            @include('auth._aside', [
                'heading' => __('Selamat datang kembali'),
                'text' => __('Masuk untuk melanjutkan membaca dan mengikuti informasi kesehatan terbaru dari :site.', ['site' => config('app.name')]),
            ])
        </x-slot:aside>

        <form action="{{ route('login') }}" method="POST">
            @csrf

            <x-input name="email" :label="__('Email')" type="email" icon="envelope" :placeholder="__('nama@email.com')"
                     required autofocus autocomplete="email" />
            <x-input name="password" :label="__('Password')" type="password" icon="lock" :placeholder="__('Masukkan password')"
                     required autocomplete="current-password" />

            <div class="mb-6 flex items-center justify-between gap-3">
                <label class="flex cursor-pointer items-center gap-2 text-sm text-gray-600">
                    <input type="checkbox" name="remember" value="1" class="size-4 rounded border-gray-300 accent-brand-600" @checked(old('remember'))>
                    {{ __('Ingat saya') }}
                </label>
                <a href="{{ route('password.request') }}" class="link text-sm">{{ __('Lupa password?') }}</a>
            </div>

            <button type="submit" class="btn btn-primary w-full py-2.5">
                <x-icon name="login" size="size-4" /> {{ __('Masuk') }}
            </button>
        </form>

        <div class="my-6 flex items-center gap-3 text-xs text-gray-500">
            <span class="h-px flex-1 bg-gray-200"></span> {{ __('Belum punya akun?') }} <span class="h-px flex-1 bg-gray-200"></span>
        </div>
        <a href="{{ route('register') }}" class="btn btn-secondary w-full py-2.5">
            <x-icon name="user-plus" size="size-4" /> {{ __('Daftar akun baru') }}
        </a>

        <x-slot:footer>
            <a href="{{ route('home') }}" class="link inline-flex items-center gap-1.5">
                <x-icon name="arrow-left" size="size-3" /> {{ __('Kembali ke beranda') }}
            </a>
        </x-slot:footer>
    </x-auth-card>
@endsection
