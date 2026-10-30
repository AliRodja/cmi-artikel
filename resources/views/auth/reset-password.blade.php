@extends('layouts.app')

@section('title', __('Reset Password'))

@section('content')
    <x-auth-card :title="__('Buat password baru')" :subtitle="__('Masukkan password baru untuk akun Anda.')">
        <x-slot:aside>
            @include('auth._aside', [
                'heading' => __('Satu langkah lagi'),
                'text' => __('Buat password baru minimal 8 karakter, lalu masuk kembali untuk melanjutkan membaca.'),
            ])
        </x-slot:aside>

        <form action="{{ route('password.update') }}" method="POST">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <x-input name="email" :label="__('Email')" type="email" icon="envelope" :value="$email" required autocomplete="email" />
            <div class="grid gap-x-4 sm:grid-cols-2">
                <x-input name="password" :label="__('Password baru')" type="password" icon="lock" :placeholder="__('Buat password')"
                         :hint="__('Minimal 8 karakter.')" required minlength="8" autofocus autocomplete="new-password" />
                <x-input name="password_confirmation" :label="__('Konfirmasi password')" type="password" icon="lock" :placeholder="__('Ulangi password')"
                         required autocomplete="new-password" />
            </div>
            @include('auth._password-match')

            <button type="submit" class="btn btn-primary mt-2 w-full py-2.5">
                <x-icon name="key" size="size-4" /> {{ __('Simpan password baru') }}
            </button>
        </form>

        <x-slot:footer>
            <a href="{{ route('login') }}" class="link inline-flex items-center gap-1.5">
                <x-icon name="arrow-left" size="size-3" /> {{ __('Kembali ke halaman Masuk') }}
            </a>
        </x-slot:footer>
    </x-auth-card>
@endsection
