@extends('layouts.app')

@section('title', 'Lupa Password')

@section('content')
    <x-auth-card title="Lupa Password" subtitle="Masukkan email akun Anda. Kami akan mengirim link untuk mengatur ulang password.">
        <form action="{{ route('password.email') }}" method="POST">
            @csrf

            <x-input name="email" label="Email" type="email" required autofocus autocomplete="email" />

            <button type="submit" class="btn btn-primary mt-2 w-full">Kirim Link Reset Password</button>
        </form>

        <x-slot:footer>
            Ingat password? <a href="{{ route('login') }}" class="link">Masuk</a>
        </x-slot:footer>
    </x-auth-card>
@endsection
