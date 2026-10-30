@extends('layouts.app')

@section('title', __('Lupa Password'))

@php
    // Setelah link terkirim (flash "status" dari ForgotPasswordController), kartu berganti ke tampilan "Cek email Anda".
    $linkSent = session()->has('status');
@endphp

@section('content')
    <x-auth-card :title="$linkSent ? __('Cek email Anda') : __('Lupa password')"
                 :subtitle="$linkSent ? __('Link untuk membuat password baru sudah dikirim.') : __('Masukkan email akun Anda. Kami akan mengirim link untuk membuat password baru.')">
        <x-slot:aside>
            @include('auth._aside', [
                'heading' => __('Lupa password?'),
                'text' => __('Tidak masalah. Kami bantu Anda membuat password baru melalui link yang dikirim ke email Anda.'),
            ])
        </x-slot:aside>

        @if ($linkSent)
            <div class="flex items-start gap-4 rounded-xl border border-green-200 bg-green-50 p-4">
                <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-white text-green-600">
                    <x-icon name="envelope-open" size="size-5" />
                </span>
                <p class="text-sm text-green-900">
                    {{ __('Jika') }} <span class="font-semibold break-all">{{ old('email') }}</span> {{ __('terdaftar, kami sudah mengirim link ke email tersebut.') }}
                    {{ __('Link berlaku') }} <span class="font-semibold">{{ __(':count menit', ['count' => config('auth.passwords.users.expire')]) }}</span>.
                </p>
            </div>

            <ul class="mt-5 space-y-2 text-sm text-gray-600">
                <li class="flex gap-2">
                    <x-icon name="check" size="size-3" class="mt-1 text-brand-600" />
                    <span>{{ __('Buka email tersebut, lalu klik tombol') }} <span class="font-medium text-gray-900">{{ __('Reset Password') }}</span>.</span>
                </li>
                <li class="flex gap-2">
                    <x-icon name="check" size="size-3" class="mt-1 text-brand-600" />
                    <span>{{ __('Belum masuk? Periksa folder Spam atau Promosi, dan pastikan email yang Anda ketik benar. Tunggu 1 menit sebelum kirim ulang.') }}</span>
                </li>
            </ul>

            <a href="{{ route('login') }}" class="btn btn-primary mt-6 w-full py-2.5">
                <x-icon name="login" size="size-4" /> {{ __('Kembali ke halaman Masuk') }}
            </a>

            {{-- Kirim ulang memakai form yang sama. Laravel membatasi 1 permintaan per 60 detik per email. --}}
            <form action="{{ route('password.email') }}" method="POST" class="mt-3">
                @csrf
                <input type="hidden" name="email" value="{{ old('email') }}">
                <button type="submit" class="btn btn-secondary w-full py-2.5">
                    <x-icon name="rotate" size="size-4" /> {{ __('Kirim ulang link') }}
                </button>
            </form>
        @else
            <form action="{{ route('password.email') }}" method="POST">
                @csrf

                <x-input name="email" :label="__('Email')" type="email" icon="envelope" :placeholder="__('nama@email.com')"
                         :hint="__('Gunakan email yang Anda pakai saat mendaftar.')" required autofocus autocomplete="email" />

                <button type="submit" class="btn btn-primary mt-2 w-full py-2.5">
                    <x-icon name="paper-plane" size="size-4" /> {{ __('Kirim link reset password') }}
                </button>
            </form>

            <a href="{{ route('login') }}" class="link mt-6 flex items-center justify-center gap-1.5 text-sm">
                <x-icon name="arrow-left" size="size-3" /> {{ __('Ingat password? Kembali ke halaman Masuk') }}
            </a>
        @endif

        <x-slot:footer>
            <a href="{{ route('home') }}" class="link inline-flex items-center gap-1.5">
                <x-icon name="arrow-left" size="size-3" /> {{ __('Kembali ke beranda') }}
            </a>
        </x-slot:footer>
    </x-auth-card>
@endsection
