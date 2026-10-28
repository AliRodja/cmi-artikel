@extends('layouts.app')

@section('title', 'Notifikasi')

@section('hero')
    <x-page-header title="Notifikasi" subtitle="Kabar terbaru tentang artikel dan komentar untuk Anda.">
        @if (auth()->user()->unreadNotifications()->exists())
            <x-slot:actions>
                <form action="{{ route('notifications.read-all') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-secondary">
                        <x-icon name="check" size="size-4" /> Tandai semua dibaca
                    </button>
                </form>
            </x-slot:actions>
        @endif
    </x-page-header>
@endsection

@section('content')
    <div class="mx-auto max-w-3xl">
        <div class="card">
            @if ($notifications->isEmpty())
                <div class="px-6 py-14 text-center">
                    <span class="mx-auto flex size-14 items-center justify-center rounded-full bg-brand-50 text-brand-600">
                        <x-icon name="bell" size="size-7" />
                    </span>
                    <p class="mt-4 font-semibold text-gray-900">Belum ada notifikasi</p>
                    <p class="mt-1 text-sm text-gray-500">Notifikasi akan muncul di sini.</p>
                </div>
            @else
                <div class="space-y-1 p-2">
                    @foreach ($notifications as $notification)
                        <x-notification-item :notification="$notification" />
                    @endforeach
                </div>
            @endif
        </div>

        <div class="mt-6">{{ $notifications->links() }}</div>
    </div>
@endsection
