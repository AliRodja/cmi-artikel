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
            {{-- Diisi JS di bawah: memberi tahu apakah kedua password sudah sama sebelum form dikirim. --}}
            <p id="password-match" class="-mt-2 mb-4 flex items-center gap-1.5 text-xs font-medium" aria-live="polite" hidden></p>

            <button type="submit" class="btn btn-primary mt-2 w-full py-2.5">
                <x-icon name="user-plus" size="size-4" /> Daftar
            </button>
        </form>

        <div class="my-6 flex items-center gap-3 text-xs text-gray-400">
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

@push('scripts')
    <script>
        // Cek "password cocok" langsung saat mengetik konfirmasi. Validasi sebenarnya tetap di server (rule "confirmed").
        (() => {
            const password = document.getElementById('password');
            const confirmation = document.getElementById('password_confirmation');
            const status = document.getElementById('password-match');

            const update = () => {
                if (confirmation.value === '') {
                    status.hidden = true;
                    return;
                }
                const match = confirmation.value === password.value;
                status.hidden = false;
                status.textContent = match ? '✓ Password cocok' : 'Password belum sama';
                status.classList.toggle('text-green-700', match);
                status.classList.toggle('text-amber-700', !match);
            };

            password.addEventListener('input', update);
            confirmation.addEventListener('input', update);
        })();
    </script>
@endpush
