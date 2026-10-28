@extends('layouts.app')

@section('title', 'Daftar')

@section('content')
    <x-auth-card title="Daftar" subtitle="Buat akun untuk mulai menulis artikel.">
        <form action="{{ route('register') }}" method="POST">
            @csrf

            <x-input name="name" label="Nama" required autofocus autocomplete="name" />
            <x-input name="email" label="Email" type="email" required autocomplete="email" />
            <x-input name="password" label="Password" type="password" required autocomplete="new-password" />
            <x-input name="password_confirmation" label="Konfirmasi Password" type="password" required autocomplete="new-password" />

            <button type="submit" class="btn btn-primary mt-2 w-full">Daftar</button>
        </form>

        <x-slot:footer>
            Sudah punya akun? <a href="{{ route('login') }}" class="link">Masuk</a>
        </x-slot:footer>
    </x-auth-card>
@endsection
