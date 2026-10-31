@props(['title', 'subtitle' => null])

{{--
    Kartu halaman autentikasi (masuk, daftar, lupa & reset password).
    Slot opsional "aside": panel biru di sisi kiri (hanya layar lebar), mis. sambutan di halaman Masuk.
    Kolom isian & tombol di dalam kartu otomatis bersudut lebih bulat (rounded-xl) agar seragam di semua halaman auth.
    Kartu dipusatkan vertikal hanya mulai layar sm; di ponsel kartu langsung di atas agar tombol bawah form tidak
    tertutup tombol kontak melayang.
--}}
<div class="flex items-center justify-center sm:min-h-[calc(100vh-8rem)] sm:py-12">
    <div @class(['mx-auto w-full', 'max-w-md' => ! isset($aside), 'max-w-4xl' => isset($aside)])>
        <div @class([
            'overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-xl shadow-slate-900/5',
            'lg:grid lg:grid-cols-[5fr_6fr]' => isset($aside),
        ])>
            @isset($aside)
                <aside class="relative hidden overflow-hidden bg-brand-950 p-10 text-white lg:flex lg:flex-col">
                    {{-- Cahaya lembut di pojok kiri atas, sengaja tipis (bukan gradien mencolok). --}}
                    <div class="pointer-events-none absolute -top-24 -left-24 size-64 rounded-full bg-brand-800/30 blur-3xl"></div>
                    <div class="relative flex h-full flex-col">{{ $aside }}</div>
                </aside>
            @endisset

            <div>
                <div @class(['h-1.5 bg-brand-600', 'lg:hidden' => isset($aside)])></div>
                <div class="p-6 sm:p-10 lg:p-12 [&_.btn]:rounded-xl [&_.form-input]:rounded-xl [&_.form-input]:border-slate-200">
                    {{-- Di layar lebar logo sudah tampil di panel kiri (aside). --}}
                    <x-logo :class="isset($aside) ? 'mb-6 lg:hidden' : 'mb-6'" />
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ $title }}</h1>
                    @if ($subtitle)
                        <p class="mt-1.5 text-sm text-slate-500">{{ $subtitle }}</p>
                    @endif

                    <div class="mt-8">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>

        @isset($footer)
            <p class="mt-6 text-center text-sm text-gray-600">{{ $footer }}</p>
        @endisset
    </div>
</div>
