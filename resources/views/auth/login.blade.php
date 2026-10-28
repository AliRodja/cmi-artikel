@extends('layouts.app')

@section('title', 'Masuk')

@section('content')
    <x-auth-card title="Masuk" subtitle="Masuk untuk mengelola artikel Anda.">
        <form action="{{ route('login') }}" method="POST">
            @csrf

            <x-input name="email" label="Email" type="email" required autofocus autocomplete="email" />
            <x-input name="password" label="Password" type="password" required autocomplete="current-password" />

            <div class="mb-6 flex items-center justify-between">
                <label class="flex items-center gap-2 text-sm text-gray-600">
                    <input type="checkbox" name="remember" value="1" class="rounded border-gray-300" @checked(old('remember'))>
                    Ingat saya
                </label>
                <a href="{{ route('password.request') }}" class="link text-sm">Lupa password?</a>
            </div>

            <button type="submit" class="btn btn-primary w-full">Masuk</button>
        </form>

        <x-slot:footer>
            Belum punya akun? <a href="{{ route('register') }}" class="link">Daftar</a>
        </x-slot:footer>
    </x-auth-card>
@endsection
