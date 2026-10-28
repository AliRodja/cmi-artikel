@extends('layouts.app')

@section('title', 'Reset Password')

@section('content')
    <x-auth-card title="Reset Password" subtitle="Buat password baru untuk akun Anda.">
        <form action="{{ route('password.update') }}" method="POST">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <x-input name="email" label="Email" type="email" :value="$email" required autocomplete="email" />
            <x-input name="password" label="Password Baru" type="password" required autofocus autocomplete="new-password" />
            <x-input name="password_confirmation" label="Konfirmasi Password Baru" type="password" required autocomplete="new-password" />

            <button type="submit" class="btn btn-primary mt-2 w-full">Simpan Password Baru</button>
        </form>
    </x-auth-card>
@endsection
