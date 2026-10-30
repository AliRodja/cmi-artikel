@extends('layouts.app')

@section('title', __('Kelola Akun'))

@section('hero')
    <x-page-header :title="__('Kelola Akun')" :subtitle="__('Ubah data akun dan password Anda.')" />
@endsection

@section('content')
    <div class="grid items-start gap-6 lg:grid-cols-3">
        {{-- Ringkasan akun --}}
        <aside class="card p-6 text-center lg:sticky lg:top-24 lg:order-last">
            <x-avatar :name="$user->name" size="lg" class="mx-auto" />
            <p class="mt-3 font-bold text-gray-900">{{ $user->name }}</p>
            <p class="truncate text-sm text-gray-500">{{ $user->email }}</p>
            <x-role-badge :user="$user" class="mt-2" />

            <dl class="mt-6 grid grid-cols-3 gap-2 border-t border-gray-100 pt-5 text-center">
                <div>
                    <dt class="text-xs text-gray-500">{{ __('Artikel') }}</dt>
                    <dd class="text-lg font-bold text-gray-900">{{ $user->articles_count }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500">{{ __('Komentar') }}</dt>
                    <dd class="text-lg font-bold text-gray-900">{{ $user->comments_count }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500">{{ __('Tersimpan') }}</dt>
                    <dd class="text-lg font-bold text-gray-900">{{ $user->bookmarks_count }}</dd>
                </div>
            </dl>
            <p class="mt-5 text-xs text-gray-500">{{ __('Bergabung') }} {{ $user->created_at->translatedFormat('d F Y') }}</p>
        </aside>

        <div class="min-w-0 space-y-6 lg:col-span-2">
            <form action="{{ route('profile.update') }}" method="POST" class="card p-6">
                @csrf
                @method('PATCH')
                <h2 class="font-bold text-gray-900">{{ __('Data Akun') }}</h2>
                <p class="mt-1 mb-4 text-xs text-gray-500">{{ __('Nama tampil sebagai penulis artikel dan pada komentar Anda.') }}</p>

                <x-input name="name" :label="__('Nama')" :value="$user->name" required maxlength="255" autocomplete="name" />
                <x-input name="profession" :label="__('Gelar / profesi (opsional)')" icon="stethoscope" :value="$user->profession"
                     :placeholder="__('mis. Dokter Spesialis Jantung (Sp.JP)')" maxlength="100"
                     :hint="__('Tampil di bawah nama penulis pada artikel.')" />
                <x-input name="email" :label="__('Email')" type="email" :value="$user->email" required maxlength="255" autocomplete="email" />

                <div class="flex flex-wrap items-center justify-between gap-3">
                    <p class="text-xs text-gray-500">
                        {{ __('Peran') }}: <span class="font-medium text-gray-700">{{ __($user->role_label) }}</span> · {{ __('diatur oleh admin.') }}
                    </p>
                    <button type="submit" class="btn btn-primary">{{ __('Simpan Perubahan') }}</button>
                </div>
            </form>

            <form action="{{ route('profile.password') }}" method="POST" class="card p-6">
                @csrf
                @method('PUT')
                <h2 class="font-bold text-gray-900">{{ __('Ganti Password') }}</h2>
                <p class="mt-1 mb-4 text-xs text-gray-500">{{ __('Masukkan password saat ini, lalu password baru minimal 8 karakter.') }}</p>

                <x-input name="current_password" :label="__('Password saat ini')" type="password" required autocomplete="current-password" />
                <div class="grid gap-x-4 sm:grid-cols-2">
                    <x-input name="password" :label="__('Password baru')" type="password" required autocomplete="new-password" />
                    <x-input name="password_confirmation" :label="__('Konfirmasi password baru')" type="password" required autocomplete="new-password" />
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="btn btn-primary">{{ __('Ganti Password') }}</button>
                </div>
            </form>
        </div>
    </div>
@endsection
