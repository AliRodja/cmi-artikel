@extends('layouts.app')

@section('title', 'Daftar')

@section('content')
    <x-auth-card title="Buat akun baru" subtitle="Gratis. Cukup isi nama, email, dan password.">
        <x-slot:aside>
            @include('auth._aside', [
                'heading' => 'Bergabung bersama kami',
                'text' => 'Buat akun untuk menyimpan artikel, ikut berdiskusi, dan mendapat kabar kesehatan terbaru dari '.config('app.name').'.',
            ])
        </x-slot:aside>

        <form action="{{ route('register') }}" method="POST">
            @csrf

            <x-input name="name" label="Nama lengkap" icon="user" placeholder="Nama Anda"
                     required autofocus autocomplete="name" maxlength="255" />
            <x-input name="email" label="Email" type="email" icon="envelope" placeholder="nama@email.com"
                     required autocomplete="email" maxlength="255" />
            <div class="grid gap-x-4 sm:grid-cols-2">
                <x-input name="password" label="Password" type="password" icon="lock" placeholder="Buat password"
                         hint="Minimal 8 karakter." required minlength="8" autocomplete="new-password" />
                <x-input name="password_confirmation" label="Konfirmasi password" type="password" icon="lock" placeholder="Ulangi password"
                         required autocomplete="new-password" />
            </div>
            @include('auth._password-match')

            <button type="submit" class="btn btn-primary mt-2 w-full py-2.5">
                <x-icon name="user-plus" size="size-4" /> Daftar
            </button>
        </form>

        <div class="my-6 flex items-center gap-3 text-xs text-gray-500">
            <span class="h-px flex-1 bg-gray-200"></span> Sudah punya akun? <span class="h-px flex-1 bg-gray-200"></span>
        </div>
        <a href="{{ route('login') }}" class="btn btn-secondary w-full py-2.5">
            <x-icon name="login" size="size-4" /> Masuk
        </a>

        <x-slot:footer>
            <a href="{{ route('home') }}" class="link inline-flex items-center gap-1.5">
                <x-icon name="arrow-left" size="size-3" /> Kembali ke beranda
            </a>
        </x-slot:footer>
    </x-auth-card>
@endsection
