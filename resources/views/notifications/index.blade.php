@extends('layouts.app')

@section('title', __('Notifikasi'))

@section('hero')
    <x-page-header :title="__('Notifikasi')" :subtitle="__('Kabar terbaru tentang artikel dan komentar untuk Anda.')">
        @if ($unreadCount > 0)
            <x-slot:actions>
                <form action="{{ route('notifications.read-all') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-secondary">
                        <x-icon name="check" size="size-4" /> {{ __('Tandai semua dibaca') }}
                    </button>
                </form>
            </x-slot:actions>
        @endif
    </x-page-header>
@endsection

@section('content')
    @php
        $tabs = [
            ['label' => __('Semua'), 'filter' => null, 'count' => $totalCount],
            ['label' => __('Belum dibaca'), 'filter' => 'belum-dibaca', 'count' => $unreadCount],
        ];
        // Kelompok waktu: Hari ini / Kemarin / tanggal — lebih mudah dipahami daripada daftar panjang tanpa jeda.
        $groups = collect($notifications->items())->groupBy(fn ($notification) => match (true) {
            $notification->created_at->isToday() => __('Hari ini'),
            $notification->created_at->isYesterday() => __('Kemarin'),
            default => $notification->created_at->translatedFormat('d F Y'),
        });
    @endphp

    <div class="mx-auto max-w-3xl">
        <nav class="mb-6 flex gap-1 rounded-lg bg-gray-100 p-1 sm:w-fit" aria-label="{{ __('Filter notifikasi') }}">
            @foreach ($tabs as $tab)
                @php($isActive = $onlyUnread === ($tab['filter'] !== null))
                <a href="{{ route('notifications.index', array_filter(['filter' => $tab['filter']])) }}"
                   @if ($isActive) aria-current="page" @endif
                   @class([
                       'flex flex-1 items-center justify-center gap-2 rounded-md px-4 py-2 text-sm font-medium transition sm:flex-none',
                       'bg-white text-gray-900 shadow-sm' => $isActive,
                       'text-gray-600 hover:text-gray-900' => ! $isActive,
                   ])>
                    {{ $tab['label'] }}
                    <span @class([
                        'rounded-full px-1.5 text-xs',
                        'bg-brand-600 text-white' => $tab['filter'] && $tab['count'] > 0,
                        'bg-gray-200/70 text-gray-600' => ! ($tab['filter'] && $tab['count'] > 0),
                    ])>{{ $tab['count'] }}</span>
                </a>
            @endforeach
        </nav>

        @if ($notifications->isEmpty())
            <div class="card px-6 py-14 text-center">
                <span class="mx-auto flex size-16 items-center justify-center rounded-full bg-brand-50 text-brand-600">
                    <x-icon name="bell" size="size-7" />
                </span>
                <p class="mt-4 text-lg font-semibold text-gray-900">{{ $onlyUnread ? __('Semua notifikasi sudah dibaca') : __('Belum ada notifikasi') }}</p>
                <p class="mt-1 text-gray-600">
                    {{ $onlyUnread ? __('Tidak ada kabar baru untuk Anda saat ini.') : __('Kabar tentang artikel baru dan komentar akan muncul di sini.') }}
                </p>
            </div>
        @else
            <div class="space-y-6">
                @foreach ($groups as $label => $items)
                    <section>
                        <h2 class="mb-2 text-sm font-semibold tracking-wider text-gray-500 uppercase">{{ $label }}</h2>
                        <div class="card space-y-1 p-2">
                            @foreach ($items as $notification)
                                <x-notification-item :notification="$notification" />
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>
        @endif

        <div class="mt-6">{{ $notifications->links() }}</div>
    </div>
@endsection
