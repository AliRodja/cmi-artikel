{{--
    Pop-up notifikasi (toast) dari flash session: ->with('success' | 'status' | 'error', 'pesan').
    Bila ada error validasi tanpa pesan 'error', ditampilkan pop-up gagal umum.
    Berhasil (8 dtk) & informasi (10 dtk) hilang sendiri saat bar waktunya habis (berhenti selama disorot kursor);
    pop-up gagal tetap tampil sampai ditutup lewat tombol ×, agar pembaca sempat membaca penyebabnya;
    perilakunya di resources/js/app.js, animasinya di .toast pada app.css.
--}}
@php
    $toastTypes = [
        'success' => [
            'title' => __('Berhasil'),
            'icon' => 'check-circle',
            'role' => 'status',
            'iconClass' => 'bg-green-50 text-green-600',
            'timerClass' => 'bg-green-500 [--toast-duration:8s]',
        ],
        'status' => [
            'title' => __('Informasi'),
            'icon' => 'info',
            'role' => 'status',
            'iconClass' => 'bg-brand-50 text-brand-600',
            'timerClass' => 'bg-brand-500 [--toast-duration:10s]',
        ],
        'error' => [
            'title' => __('Gagal'),
            'icon' => 'exclamation-triangle',
            'role' => 'alert',
            'iconClass' => 'bg-red-50 text-red-600',
            'timerClass' => null,
        ],
    ];

    $toasts = collect($toastTypes)
        ->map(fn ($type, $key) => $type + ['message' => session($key)])
        ->filter(fn ($toast) => filled($toast['message']));

    if ($errors->any() && ! $toasts->has('error')) {
        $toasts->put('error', $toastTypes['error'] + ['message' => __('Periksa kembali isian yang ditandai merah, lalu coba lagi.')]);
    }
@endphp

@if ($toasts->isNotEmpty())
    <div class="pointer-events-none fixed inset-x-4 top-20 z-50 flex flex-col items-center gap-3 sm:inset-x-auto sm:right-6 sm:items-end">
        @foreach ($toasts as $toast)
            <div data-toast role="{{ $toast['role'] }}"
                 class="toast pointer-events-auto w-full max-w-sm overflow-hidden rounded-xl border border-gray-200 bg-white shadow-lg">
                <div class="flex items-start gap-3 p-4">
                    <span class="flex size-9 shrink-0 items-center justify-center rounded-full {{ $toast['iconClass'] }}">
                        <x-icon :name="$toast['icon']" size="size-4" />
                    </span>
                    <div class="min-w-0 flex-1 pt-0.5">
                        <p class="text-sm font-semibold text-gray-900">{{ $toast['title'] }}</p>
                        <p class="mt-0.5 text-sm text-gray-600">{{ $toast['message'] }}</p>
                    </div>
                    <button type="button" data-toast-close
                            class="-mt-1.5 -mr-1.5 flex size-10 shrink-0 cursor-pointer items-center justify-center rounded-lg text-gray-500 transition hover:bg-gray-100 hover:text-gray-700">
                        <x-icon name="x-mark" size="size-4" />
                        <span class="sr-only">{{ __('Tutup') }}</span>
                    </button>
                </div>
                @if ($toast['timerClass'])
                    <div data-toast-timer class="toast-timer h-1 {{ $toast['timerClass'] }}"></div>
                @endif
            </div>
        @endforeach
    </div>
@endif
