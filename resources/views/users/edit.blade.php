@extends('layouts.app')

@section('title', __('Edit Pengguna'))

@section('hero')
    <x-page-header :title="__('Edit Pengguna')" :subtitle="$user->email" :back-url="route('users.index')" :back-label="__('Kelola Pengguna')">
        <x-slot:meta>
            <p class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-gray-500">
                <x-role-badge :user="$user" />
                <span class="inline-flex items-center gap-1.5"><x-icon name="calendar" size="size-4" /> {{ __('Bergabung') }} {{ $user->created_at->translatedFormat('d F Y') }}</span>
                <span class="inline-flex items-center gap-1.5"><x-icon name="document-text" size="size-4" /> {{ trans_choice(':count artikel', $user->articles_count) }}</span>
            </p>
        </x-slot:meta>
    </x-page-header>
@endsection

@section('content')
    <form action="{{ route('users.update', $user) }}" method="POST">
        @csrf
        @method('PUT')

        @include('users._form', ['user' => $user, 'submitLabel' => __('Simpan Perubahan')])
    </form>
@endsection
